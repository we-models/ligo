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
    <h1>{{__('List')}}</h1>
</header>
<main>
    <table  class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                @foreach($headers as $key=>$item)
                    <th>{{$item['name']}}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    @foreach($headers as $key=>$value)
                        <td>{!! getPDFValues($item, $key) !!}</td>
                    @endforeach
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
                    $pdf->text(270, 730, "Página $PAGE_NUM de $PAGE_COUNT", $font, 10);
                ');
            }
        </script>
</footer>
</body>
</html>
