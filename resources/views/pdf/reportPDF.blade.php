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

          @if($loop->iteration == $pageLimit )
            @break;
          @endif

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

   <!-- Mark pages -->
    <script type="text/php">
      if (isset($pdf)) {
          $pdf->page_script('
                  $text = __("Page :pageNum/:pageCount", ["pageNum" => $PAGE_NUM, "pageCount" => $PAGE_COUNT]);
                  $font = null;
                  $size = 9;
                  $color = array(0,0,0);
                  $word_space = 0.0;  //  default
                  $char_space = 0.0;  //  default
                  $angle = 0.0;   //  default

                  // Compute text width to center correctly
                  $textWidth = $fontMetrics->getTextWidth($text, $font, $size);

                  $x = ($pdf->get_width() - $textWidth) / 2;
                  $y = $pdf->get_height() - 35;

                  $pdf->text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
              ');
            
      }
    </script>

  </body>
</html>