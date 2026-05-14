<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 6pt; }
        h2 { font-size: 11pt; margin-bottom: 6px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td {
            border: 1px solid #555;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }
        th {
            background-color: #428bca;
            color: #ffffff;
            font-weight: bold;
            font-size: 6pt;
        }
        td { font-size: 5.5pt; }
        .time-col { width: 48px; font-weight: bold; }
        .page-break { page-break-before: always; margin-top: 12px; }
    </style>
</head>
<body>

@foreach ([['matrix' => $upperMatrix, 'label' => 'Верхняя неделя'], ['matrix' => $lowerMatrix, 'label' => 'Нижняя неделя']] as $weekIndex => $week)
    @if ($weekIndex > 0)
        <div class="page-break"></div>
    @endif

    <h2>{{ $week['label'] }}</h2>
    <table>
        <thead>
            <tr>
                <th class="time-col" rowspan="2">Время</th>
                @foreach ($days as $dayName)
                    <th colspan="{{ count($groups) }}">{{ $dayName }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach ($days as $dayName)
                    @foreach ($groups as $groupName)
                        <th>{{ $groupName }}</th>
                    @endforeach
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($times as $partitionId => $time)
                <tr>
                    <td class="time-col">{{ $time }}</td>
                    @foreach (array_keys($days) as $dayNumber)
                        @foreach ($groupIds as $groupId)
                            @php $cell = $week['matrix'][$dayNumber][$partitionId][$groupId] ?? null; @endphp
                            <td>
                                @if ($cell && $cell['subject'])
                                    <strong>{{ $cell['subject'] }}</strong><br>
                                    {{ $cell['teacher'] }}<br>
                                    {{ $cell['room'] }}@if($cell['building']) ({{ $cell['building'] }})@endif
                                @endif
                            </td>
                        @endforeach
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach

</body>
</html>
