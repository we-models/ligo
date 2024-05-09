@php
    use Carbon\CarbonImmutable;
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{__('Report')}}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <style>
        .log_row_detail{
            border-bottom:1px solid rgba(0,0,0,0.1);
            padding-bottom:5px;
            padding-top: 5px;
        }
        td,th{
            font-size: 11px;
            padding:10px;
        }
        tr{
            border: 1px solid rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<header>
    <h1>{{__('Report')}}</h1>
</header>
    <main>
        <table  class="table table-bordered">
            <thead class="thead-dark">
            <tr>
                <th style="width: 5%">{{__('ID')}}</th>
                <th style="width: 15%">{{__('Causer')}}</th>
                <th style="width: 15%">{{__('Description')}}</th>
                <th style="width: 25%">{{__('Date time')}}</th>
                <th style="width: 40%">{{__('Details')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{$log->id}}</td>
                    <td>
                        @if(!empty($log->causer))
                            <span>{{$log->causer->name}}</span>
                            <small>{{$log->causer->email}}</small>
                        @endif
                    </td>
                    <td>
                        {{$log->description}}
                    </td>
                    <td>
                        {{CarbonImmutable::parse($log->created_at)->toDateTimeString()}}
                    </td>
                    <td>
                        @php
                            $properties = $log->properties->all();
                            if(isset($properties['attributes'])){
                                $details = $properties['attributes'];
                            }
                            if(isset($properties['old'])){
                                $details = $properties['old'];
                            }
                        @endphp
                        <div class="row">
                            @foreach($details as $key=> $value)
                                <div class="log_row_detail">
                                    <strong>{{ucfirst(implode(' ', explode('_', $key)))}}</strong> :
                                    @if(in_array($key, ['created_at', 'updated_at', 'deleted_at']) )
                                        {{CarbonImmutable::parse($value)->toDateTimeString()}}
                                    @else
                                        {!! $value !!}
                                    @endif
                                </div>

                            @endforeach
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </main>

    <footer>
        <script type="text/php">
            if ( isset($pdf) ) {
                $pdf->page_script('
                    $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                    $pdf->text(270, 730, "Pagina $PAGE_NUM de $PAGE_COUNT", $font, 10);
                ');
            }
        </script>
    </footer>
</body>
</html>
