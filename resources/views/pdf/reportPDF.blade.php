<!doctype html>
<html>
  <head>
    <title>QR Code</title>
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
      @foreach ($data as $page)
          @foreach ($page['images'] as $src)
            <img src="{!! $src !!}" />
          @endforeach
          <div class="page-break"></div>
      @endforeach
    </div>
  </body>
</html>