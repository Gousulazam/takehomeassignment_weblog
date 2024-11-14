@extends('layout.main')\
@section('title')
    Details for campaign: {{ $campaign->name }}
@endsection
@section('content')
    <h2 class="mb-4">Campaign details broken down by date and hour</h2>
    <h3>Details of campaign: {{ $campaign->name }}</h3>

    <!-- Stats table for this campaign -->
    <table class="table table-striped table-bordered" id="campaignDateAndHoursStatsTable">
        <thead>
            <tr>
                <th>Sl No</th>
                <th>Term</th>
                <th>Date</th>
                <th>Hour</th>
                <th>Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            {{-- @foreach ($stats as $stat)
                <tr>
                    <td>{{ date("d-m-Y",strtotime($stat->event_date)) }}</td>
                    <td>{{date("g A", strtotime("$stat->event_hour:00")) }}</td>
                    <td>{{ number_format($stat->total_revenue, 4) }}</td>
                </tr>
            @endforeach --}}

        </tbody>
        <tfoot>
            @if ($grandTotalRevenue > 0)
                <tr>
                    <td colspan="4"><b>Total Revenue</b></td>
                    <td>{{ number_format($grandTotalRevenue, 4) }}</td>
                </tr>
            @endif
        </tfoot>
    </table>

    <!-- Custom dynamic pagination (only when there are more than one page) -->
    {{-- @if ($lastPage > 1)
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <!-- Previous button -->
                <li class="page-item {{ $stats->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $stats->previousPageUrl() }}" tabindex="-1">Previous</a>
                </li>

                <!-- First Page link -->
                @if ($stats->currentPage() > 3)
                    <li class="page-item">
                        <a class="page-link" href="{{ $stats->url(1) }}">1</a>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif

                <!-- Loop through a range of pages around the current page -->
                @for ($i = max(1, $stats->currentPage() - 3); $i <= min($stats->lastPage(), $stats->currentPage() + 3); $i++)
                    <li class="page-item {{ $stats->currentPage() == $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ $stats->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor

                <!-- Last Page link -->
                @if ($stats->currentPage() < $stats->lastPage() - 3)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="{{ $stats->url($stats->lastPage()) }}">{{ $stats->lastPage() }}</a>
                    </li>
                @endif

                <!-- Next button -->
                <li class="page-item {{ $stats->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $stats->nextPageUrl() }}">Next</a>
                </li>
            </ul>
        </nav>
    @endif --}}
@endsection

@section('js_scripts')
    <script>
        $(document).ready(function() {
            $('#campaignDateAndHoursStatsTable').DataTable({
                processing: true, // Show processing indicator while loading data
                serverSide: true, // Enable server-side processing
                ajax: {
                    url: '{{ url("getcampaignsdatabydateandhourwise/$campaignId") }}', // Server-side URL to fetch data
                    type: 'GET', // Use GET method for the AJAX request
                    dataSrc: 'data', // Specify where the data comes from in the response
                },
                columns: [{
                        data: null,
                        orderable: false, // Disable sorting for the serial number column
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1; // Serial number
                        }
                    },
                    {
                        data: 'term_name'
                    }, // Data for term name column
                    {
                        data: null,
                        render: function(data, type, row) {
                            let splittedDate = row.event_date.split(
                            "-"); // Dynamically create URL for each row
                            return `${splittedDate[2]}-${splittedDate[1]}-${splittedDate[0]}`;
                        }
                    }, // Data for term name column
                    {
                        data: 'event_hour'
                    }, // Data for term name column
                    {
                        data: 'total_revenue'
                    } // Data for total revenue column

                ],
                order: [
                    [4, 'desc']
                ], // Default ordering by total_revenue in descending order
                pageLength: 10, // Set default page length
                lengthMenu: [10, 25, 50, 100], // Allow different page sizes
                searchDelay: 500, // Delay search to prevent too many requests
            });
        });
    </script>
@endsection
