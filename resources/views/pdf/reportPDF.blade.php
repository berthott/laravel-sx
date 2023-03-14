<!doctype html>
<html>
  <head>
    <style>
      html, 
      body, 
      img, 
      h1 {
        width: 100%;
      }

      .page-break {
        page-break-after: always;
      }

      img {
        margin-top: 15px;
      }

      .debug {
        margin: 0 auto;
        max-width: 100%;
        border: solid 1px red;
        padding: 12px;
        overflow-wrap: break-word;
        word-wrap: break-word;
        hyphens: auto;
      }

    </style>
  </head>
  <body>
    <div style="width:100%">
      @foreach ($pages as $page)
          @foreach ($page['data'] as $src)
          
            @if($src['type'] === 'base64')
              <img src="{!! $src['result'] !!}" />
            @else
              <p>{!! $src['result'] !!}</p>
            @endif

          @endforeach

          @if ($loop->last)
            <!-- Last page -->
          @else
            <div class="page-break"></div>
          @endif

      @endforeach
    </div>
  </body>
</html>