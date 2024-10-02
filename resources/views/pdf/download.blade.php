<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <main style="margin: auto;" id="printable-content">
        <div class="space-y-4" style="margin: auto;">
            @foreach ($items as $item)
                @php
                    // Check if content is already an array, if not, decode it
                    $content = is_array($item->content) ? $item->content : json_decode($item->content, true);

                    // Fetch the related template to get the header and footer images
                    $template = $item->template; // This uses the defined relationship
                @endphp

                @if ($content)
                    <div class="page-section" style="position: relative; padding-top: 10mm; padding-bottom: 10mm"> <!-- Add padding for both images -->
                        @if ($template && $template->image_h) <!-- Header Image -->
                            <div class="header-image" style="position: absolute; top: 10mm; right: 0; width: 350px; ">
                                <img src="{{ asset('storage/' . $template->image_h) }}" alt="Header Image" style="width: 100%; height: auto;">
                            </div>
                        @endif
                        <div class="content" > <!-- Margin for spacing below the header image -->
                            <h1 style="text-align: center; font-weight:bolder; text-decoration:underline;">{{$template->name}}</h1>
                            @foreach ($content as $block)
                                @if ($block['type'] === 'heading')
                                   {!! $block['data']['heading'] !!}
                                @elseif ($block['type'] === 'paragraph')
                                    {!! $block['data']['Paragraph'] !!}
                                @endif
                            @endforeach
                        </div>

                        @if ($template && $template->image_f) <!-- Footer Image -->
                            <div class="footer-image" style="position: absolute; bottom: 0; left: 0; right: 0; text-align: center; width:500px; margin: auto;">
                                <img src="{{ asset('storage/' . $template->image_f) }}" alt="Footer Image" style="width: 100%; height: auto;">
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>

            <style>

                /* Ensure that the content fits within A4 paper dimensions on screen and print */
                .page-section {
                    width: 210mm;
                    height: 297mm;
                    padding: 10mm;
                    background-color: white;
                    box-sizing: border-box;
                    border: 4px solid black;
                    position: relative;
                }
                .page-section:not(:first-child) {
                page-break-before: always;
            }
                .header-image{
                        padding-right: 10mm;
                        text-align: right;
                    }
                .page-section h1 {
                    font-size: 24px;
                    margin-bottom: 20px;
                }

                .page-section p {
                    font-size: 16px;
                    margin-bottom: 15px;
                }
            </style>
    </main>
</body>
</html>
