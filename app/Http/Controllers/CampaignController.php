<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    /**
     * Display list of campaigns and aggregate revenue for each campaign
     */
    public function index()
    {
        // @TODO implement
        // $campaigns = DB::table('campaigns')
        //     ->leftJoin('stats', 'campaigns.id', '=', 'stats.campaign_id')
        //     ->select('campaigns.name', 'campaigns.id', DB::raw('SUM(stats.revenue) as total_revenue'))
        //     ->groupBy('campaigns.id', 'campaigns.name')
        //     ->orderByDesc('total_revenue')
        //     ->paginate(10);
        $grandTotalRevenue = DB::table('stats')
            ->sum('revenue');
        $data['grandTotalRevenue'] = $grandTotalRevenue;
        // $data['campaigns'] = $campaigns;
        return view('campaign.index', $data);
    }

    /**
     * Display a specific campaign with a hourly breakdown of all revenue
     */
    public function show($campaignId)
    {
        $data['campaignId'] = $campaignId;
        $campaignId = base64_decode($campaignId);
        // Retrieve revenue breakdown by date and hour for the specific campaign
        // $stats = DB::table('stats')
        //     ->select('event_date', 'event_hour', DB::raw('SUM(revenue) as total_revenue'))
        //     ->where('campaign_id', $campaignId)
        //     ->groupBy('event_date', 'event_hour')
        //     ->orderBy('monetization_timestamp', 'desc'); // Adjust ordering as needed

        $grandTotalRevenue = DB::table('stats')
            ->where('campaign_id', $campaignId)
            ->sum('revenue');

        // Check if pagination is necessary (i.e., more than 1 page)
        // $lastPage = 1;
        // if (count($stats->get()) > 10) {
        //     $stats = $stats->paginate(10); // Paginate if more than 10 records
        //     $lastPage = $stats->lastPage();
        // } else {
        //     $stats = $stats->get(); // Get all if there's less than 10 records
        // }

        $campaign = DB::table('campaigns')->where('id', $campaignId)->first();
        $data['campaign'] = $campaign;
        // $data['stats'] = $stats;
        // $data['lastPage'] = $lastPage;
        $data['grandTotalRevenue'] = $grandTotalRevenue;

        return view('campaign.show', $data);
    }

    /**
     * Display a specific campaign with the aggregate revenue by utm_term
     */
    public function publishers($campaignId)
    {
        $data['campaignId'] = $campaignId;
        $campaignId = base64_decode($campaignId);

        // Retrieve revenue breakdown by date and hour for the specific campaign
        // $stats = DB::table('stats')
        // ->join('terms', 'stats.term_id', '=', 'terms.id') // Join the terms table
        // ->select('terms.name AS term_name', DB::raw('SUM(revenue) as total_revenue'))
        // ->where('stats.campaign_id', $campaignId)
        // ->groupBy('terms.name') // Group by term_name, event_date, and event_hour
        // ->orderBy('monetization_timestamp', 'desc'); // Adjust ordering as needed

        $grandTotalRevenue = DB::table('stats')
            ->where('campaign_id', $campaignId)
            ->sum('revenue');

        // Check if pagination is necessary (i.e., more than 1 page)
        // $lastPage = 1;
        // if (count($stats->get()) > 10) {
        //     $stats = $stats->paginate(10); // Paginate if more than 10 records
        //     $lastPage = $stats->lastPage();
        // } else {
        //     $stats = $stats->get(); // Get all if there's less than 10 records
        // }

        $campaign = DB::table('campaigns')->where('id', $campaignId)->first();

        $data['campaign'] = $campaign;
        $data['grandTotalRevenue'] = $grandTotalRevenue;

        // $data['stats'] = $stats;
        // $data['lastPage'] = $lastPage;

        // dd($data);
        return view('campaign.publishers', $data);
    }

    public function getCampaignsData(Request $request)
    {
        // Base query for fetching data
        $query = DB::table('campaigns')
            ->leftJoin('stats', 'campaigns.id', '=', 'stats.campaign_id')
            ->select('campaigns.id', 'campaigns.name', DB::raw('SUM(stats.revenue) as total_revenue'))
            ->groupBy('campaigns.id', 'campaigns.name');

        // Apply searching on the main data query
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->orWhere('campaigns.name', 'like', "%{$search}%");
            });

            // If search term is numeric, apply filtering on total_revenue
            if (is_numeric($search)) {
                $query->havingRaw("SUM(stats.revenue) = ?", [$search]);
            }
        }

        $columnsForOrderBy = ['campaigns.name', 'SUM(stats.revenue)'];

        if ($request->has('order')) {
            $columnIndex = $request->order[0]['column'];
            $columnName = $columnsForOrderBy[$columnIndex];
            $columnSortOrder = $request->order[0]['dir']; // 'asc' or 'desc'
            $query->orderByRaw("$columnName $columnSortOrder");
        } else {
            // Default ordering by total_revenue in descending order
            $query->orderByRaw('SUM(stats.revenue) DESC');
        }

        $recordsFiltered = count($query->get());
        $recordsTotal = DB::table('campaigns')->count();

        $start = $request->input('start', 0);
        $limit = $request->input('length', 10);
        $query = $query->offset($start)->limit($limit);

        $campaigns = $query->get();

        // Prepare the response for DataTables
        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $campaigns
        ]);
    }

    function getCampaignsDataByTermWise(Request $request, $campaignId)
    {
        $campaignId = base64_decode($campaignId);

        $query = DB::table('stats')
            ->join('terms', 'stats.term_id', '=', 'terms.id') // Join the terms table
            ->select('terms.name AS term_name', DB::raw('SUM(revenue) as total_revenue'))
            ->where('stats.campaign_id', $campaignId)
            ->groupBy('terms.name') // Group by term_name, event_date, and event_hour
            ->orderBy('monetization_timestamp', 'desc'); // Adjust ordering as needed

        // Apply searching on the main data query
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->orWhere('terms.name', 'like', "%{$search}%");
            });

            // If search term is numeric, apply filtering on total_revenue
            if (is_numeric($search)) {
                $query->havingRaw("SUM(stats.revenue) = ?", [$search]);
            }
        }

        $columnsForOrderBy = ['terms.name', 'SUM(stats.revenue)'];

        if ($request->has('order')) {
            $columnIndex = $request->order[0]['column'];
            $columnName = $columnsForOrderBy[$columnIndex];
            $columnSortOrder = $request->order[0]['dir']; // 'asc' or 'desc'
            $query->orderByRaw("$columnName $columnSortOrder");
        } else {
            // Default ordering by total_revenue in descending order
            $query->orderByRaw('SUM(stats.revenue) DESC');
        }

        $recordsFiltered = count($query->get());
        $recordsTotal = DB::table('campaigns')->count();

        $start = $request->input('start', 0);
        $limit = $request->input('length', 10);
        $query = $query->offset($start)->limit($limit);

        $campaignsByTermWise = $query->get();

        // Prepare the response for DataTables
        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $campaignsByTermWise
        ]);
    }

    function getCampaignsDataByDateAndHourWise(Request $request, $campaignId)
    {
        $campaignId = base64_decode($campaignId);
        // Retrieve revenue breakdown by date and hour for the specific campaign
        $query = DB::table('stats')
            ->join('terms', 'stats.term_id', '=', 'terms.id')
            ->select('terms.name AS term_name', 'stats.event_date', 'stats.event_hour', DB::raw('SUM(revenue) as total_revenue'))
            ->where('campaign_id', $campaignId)
            ->groupBy('event_date', 'event_hour')
            ->orderBy('monetization_timestamp', 'desc'); // Adjust ordering as needed

        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->orWhere('terms.name', 'like', "%{$search}%");
                $q->orWhere('stats.event_date', 'like', "%{$search}%");
                $q->orWhere('stats.event_hour', 'like', "%{$search}%");
            });

            // If search term is numeric, apply filtering on total_revenue
            if (is_numeric($search)) {
                $query->havingRaw("SUM(stats.revenue) = ?", [$search]);
            }
        }

        $columnsForOrderBy = ["",'terms.name', 'stats.event_date', 'stats.event_hour', 'SUM(stats.revenue)'];

        if ($request->has('order')) {
            $columnIndex = $request->order[0]['column'];
            $columnIndex > 1 ? $columnIndex--:$columnIndex;
            $columnName = $columnsForOrderBy[$columnIndex];
            $columnSortOrder = $request->order[0]['dir']; // 'asc' or 'desc'
            $query->orderByRaw("$columnName $columnSortOrder");
        } else {
            // Default ordering by total_revenue in descending order
            $query->orderByRaw('SUM(stats.revenue) DESC');
        }

        $recordsFiltered = count($query->get());
        $recordsTotal = DB::table('campaigns')->count();

        $start = $request->input('start', 0);
        $limit = $request->input('length', 10);
        $query = $query->offset($start)->limit($limit);

        $campaignsByDateHourWise = $query->get();

        // Prepare the response for DataTables
        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $campaignsByDateHourWise
        ]);
    }
}
