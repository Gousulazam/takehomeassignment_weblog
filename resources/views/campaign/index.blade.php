@extends('layout.main')
@section('title')
    Aggregated Revenue by Campaigns
@endsection
@section('content')
    <h2>Aggregated Revenue by Campaigns</h2>
    <table class="table table-bordered table-striped" id="campaignsTable">
        <thead>
            <tr>
                <th>Sl No</th>
                <th>Campaign</th>
                <th>Total Revenue</th>
                <th class="text-center">Stats By Date and Hour</th>
                <th>Stats By Terms</th>
            </tr>
        </thead>
        <tbody>
            {{-- @foreach ($campaigns as $campaign)
            @php
                $showUrl = 'campaigns/'.base64_encode($campaign->id);
                $publisherUrl = 'campaigns/'.base64_encode($campaign->id).'/publishers';
            @endphp
            <tr>
                <td>{{ $campaign->name }}</td>
                <td>{{ number_format($campaign->total_revenue, 4) }}</td>
                <td>
                    <center>
                        <a class="btn btn-primary btn-sm rounded" target="_blank" href="{{ url($showUrl) }}">View</a>
                    </center>
                </td>
                <td>
                    <center>
                        <a class="btn btn-primary btn-sm rounded" target="_blank" href="{{ url($publisherUrl) }}">View</a>
                    </center>
                </td>
            </tr>
        @endforeach --}}
        </tbody>
        <tfoot>
            @if ($grandTotalRevenue > 0)
                <tr>
                    <td colspan="2"><b>Total Revenue</b></td>
                    <td>{{ number_format($grandTotalRevenue, 4) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endif
        </tfoot>
    </table>

    <!-- Custom dynamic pagination -->
    {{-- <nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center">
        <!-- Previous button -->
        <li class="page-item {{ $campaigns->onFirstPage() ? 'disabled' : '' }}">
            <a class="page-link" href="{{ $campaigns->previousPageUrl() }}" tabindex="-1">Previous</a>
        </li>

        <!-- First Page link -->
        @if ($campaigns->currentPage() > 3)
            <li class="page-item">
                <a class="page-link" href="{{ $campaigns->url(1) }}">1</a>
            </li>
            <li class="page-item disabled">
                <span class="page-link">...</span>
            </li>
        @endif

        <!-- Loop through a range of pages around the current page -->
        @for ($i = max(1, $campaigns->currentPage() - 3); $i <= min($campaigns->lastPage(), $campaigns->currentPage() + 3); $i++)
            <li class="page-item {{ $campaigns->currentPage() == $i ? 'active' : '' }}">
                <a class="page-link" href="{{ $campaigns->url($i) }}">{{ $i }}</a>
            </li>
        @endfor

        <!-- Last Page link -->
        @if ($campaigns->currentPage() < $campaigns->lastPage() - 3)
            <li class="page-item disabled">
                <span class="page-link">...</span>
            </li>
            <li class="page-item">
                <a class="page-link" href="{{ $campaigns->url($campaigns->lastPage()) }}">{{ $campaigns->lastPage() }}</a>
            </li>
        @endif

        <!-- Next button -->
        <li class="page-item {{ $campaigns->hasMorePages() ? '' : 'disabled' }}">
            <a class="page-link" href="{{ $campaigns->nextPageUrl() }}">Next</a>
        </li>
    </ul>
</nav> --}}
@endsection
@section('js_scripts')
    <script>
        $(document).ready(function() {
            // alert('dd');
            $('#campaignsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('campaign/data')}}",
                    type: 'GET'
                },
                columns: [
                    {
                        data: null,
                        orderable: false, // Disable sorting for the serial number column
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1; // Serial number
                        }
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'total_revenue',
                        name: 'total_revenue'
                    },
                    {
                        data: null, // Placeholder for Stats By Date and Hour
                        render: function(data, type, row) {
                            let showUrl = 'campaigns/' + btoa(row.id); // Dynamically create URL for each row
                            return `<center><a class="btn btn-primary btn-sm rounded" target="_blank" href="${showUrl}">View</a></center>`;
                        }
                    },
                    {
                        data: null, // Placeholder for Stats By Terms
                        render: function(data, type, row) {
                            let publisherUrl = 'campaigns/' + btoa(row.id) + '/publishers'; // Dynamically create URL for each row
                            return `<center><a class="btn btn-primary btn-sm rounded" target="_blank" href="${publisherUrl}">View</a></center>`;
                        }
                    }
                ],
                order: [
                    [1, 'desc']
                ], // Default ordering by total_revenue
                columnDefs: [
                    { targets: [2, 3], orderable: false } // Disable sorting for these columns
                ]
            });
        });
    </script>
@endsection
