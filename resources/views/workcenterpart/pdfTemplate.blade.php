<!DOCTYPE html>
<html>
<head>
    <title>Workcenter Part Characteristics</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.4;
            background-color: white;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
        }
        .header {
            display: flex;
            padding: 1rem;
            border-bottom: 1px solid #ddd;
            position: relative;
            background-color: #f8f8f8;
        }
        .logo {
            width: 150px;
            margin-right: 1rem;
        }
        .page-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #555;
            text-align: center;
            margin: 0 auto;
            padding-top: 0.25rem;
        }
        .part-info {
            margin: 1rem;
            padding: 0.5rem 1rem;
            background-color: #f8f8f8;
            border-radius: 0.25rem;
        }
        .part-info p {
            margin: 0.4rem 0;
            font-size: 0.95rem;
        }
        .label {
            font-weight: 600;
            display: inline-block;
            width: 8rem;
            color: #555;
        }
        .value {
            font-weight: 600;
            color: #333;
        }
        .characteristics-section {
            padding: 0.5rem 1rem;
        }
        .group-container {
            margin-bottom: 1.5rem;
        }
        .group-header {
            color: #333;
            padding: 0.5rem 0;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.25rem;
            border-bottom: 1px solid #ccc;
        }
        .characteristics-table {
            width: 100%;
            border-collapse: collapse;
        }
        .characteristic-row {
            border-bottom: 1px solid #f0f0f0;
        }
        .characteristic-row:last-child {
            border-bottom: none;
        }
        .characteristic-cell {
            padding: 0.35rem 0.15rem;
            vertical-align: middle;
            font-size: 0.85rem;
        }
        .characteristic-id {
            width: 8%;
            text-align: center;
            font-weight: 600;
            color: #555;
        }
        .characteristic-name {
            width: 50%;
            padding-left: 0;
        }
        .characteristic-value {
            width: 12%;
            text-align: center;
        }
        .characteristic-tolerance {
            width: 15%;
            text-align: center;
            color: #555;
        }
        .characteristic-uom {
            width: 15%;
            text-align: left;
            color: #777;
            font-size: 0.8rem;
        }
        .footer {
            text-align: right;
            font-size: 0.75rem;
            color: #777;
            padding: 1rem;
            border-top: 1px solid #ddd;
            background-color: #f8f8f8;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem; 
        }
        .column {
            flex: 1 1 100%;
            max-width: 100%;
        }
        @media (min-width: 768px) {
            .column {
                flex: 1 1 48%;
                max-width: 48%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/images/borgstena_logo.png'))) }}" alt="Borgstena logo" class="logo">
        </div>

        <div class="part-info">
            <p><span class="label">Part Number:</span> <span class="value">{{ $partno_id }}</span></p>
            <p><span class="label">Description:</span> <span class="value">{{ $partno_description }}</span></p>
            <p><span class="label">Work Center:</span> <span class="value">{{ $workcenter_code }}</span></p>
            <p><span class="label">Description:</span> <span class="value">{{ $workcenter_name }}</span></p>
        </div>

        <div class="characteristics-section">
            @php
                $groupedCharacteristics = $characteristics->groupBy('group_name');
                $sortedGroups = $groupedCharacteristics->sortBy(function ($items, $key) {
                    return $items->first()->characteristic_group_order;
                });

                $groupCount = $sortedGroups->count();
                $leftColumnGroups = $sortedGroups->take(ceil($groupCount / 2));
                $rightColumnGroups = $sortedGroups->skip(ceil($groupCount / 2));
            @endphp

            <div class="row">
                <div class="column">
                    @foreach ($leftColumnGroups as $groupName => $chars)
                        <div class="group-container">
                            <div class="group-header">
                                {{ $groupName }}
                            </div>
                            <table class="characteristics-table">
                                <tbody>
                                    @foreach ($chars->sortBy('characteristic_order') as $char)
                                        <tr class="characteristic-row">
                                            <td class="characteristic-cell characteristic-name">{{ $char->characteristic_description }}</td>
                                            <td class="characteristic-cell characteristic-value">{{ $char->nominal_value }}</td>
                                            <td class="characteristic-cell characteristic-tolerance">± {{ $char->tolerance_value }}</td>
                                            <td class="characteristic-cell characteristic-uom">{{ $char -> uom }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>

                <div class="column">
                    @foreach ($rightColumnGroups as $groupName => $chars)
                        <div class="group-container">
                            <div class="group-header">
                                {{ $groupName }}
                            </div>
                            <table class="characteristics-table">
                                <tbody>
                                    @foreach ($chars->sortBy('characteristic_order') as $char)
                                        <tr class="characteristic-row">
                                            <td class="characteristic-cell characteristic-name">{{ $char->characteristic_description }}</td>
                                            <td class="characteristic-cell characteristic-value">{{ $char->nominal_value }}</td>
                                            <td class="characteristic-cell characteristic-tolerance">± {{ $char->tolerance_value }}</td>
                                            <td class="characteristic-cell characteristic-uom">{{ $char -> uom }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            </div>

        <div class="footer">
            <p>Generated on {{ date('d/m/Y H:i:s') }} | BORGSTENA</p>
        </div>
    </div>
</body>
</html>
