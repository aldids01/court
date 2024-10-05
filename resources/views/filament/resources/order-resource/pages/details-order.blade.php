<x-filament-panels::page class="w-1/2" style="margin: auto;" id="printable-content">
<div class="space-y-4" style="margin: auto;">
    @foreach ($items as $item)
        @php
            // Check if content is already an array, if not, decode it
            $content = is_array($item->content) ? $item->content : json_decode($item->content, true);

            // Fetch the related template to get the header and footer images
            $template = $item->template; // This uses the defined relationship
        @endphp

        @if ($content)
            <div class="page-section" style="position: relative; padding-top: 50px; padding-bottom: 160px;"> <!-- Add padding for both images -->
                @if ($template && $template->image_h) <!-- Header Image -->
                    <div class="header-image" style="position: absolute; top: 20mm; right: 0; width: 350px;">
                        <img src="{{ asset('storage/' . $template->image_h) }}" alt="Header Image" style="width: 100%; height: auto;">
                    </div>
                @endif
                <div class="content" style="margin-top: 160px;"> <!-- Margin for spacing below the header image -->
                    <h1 style="text-align: center; font-weight:bolder; text-decoration:underline;">{{$template->name}}</h1>
                    @foreach ($content as $block)
                        @if ($block['type'] === 'heading')
                            <h1>{!! $block['data']['heading'] !!}</h1>
                        @elseif ($block['type'] === 'paragraph')
                            <p>{!! $block['data']['Paragraph'] !!}</p>
                        @endif
                    @endforeach
                    <p style="display: flex; justify-content: flex-end; position: relative; top:40px;">
                        ____________________
                        <span style="position: absolute; left: 90%; transform: translateX(-50%); text-align: center; width: 100px; top: 20px;">Deponent</span>
                    </p>
                </div>

                @if ($template && $template->image_f) <!-- Footer Image -->
                    <div class="footer-image" style="position: absolute; bottom: 20mm; left: 0; right: 0; text-align: center; width: 600px; margin: auto">
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
            margin: 0 auto;
            padding: 20mm;
            background-color: white;
            box-sizing: border-box;
            border: 4px solid black;
        }
        .header-image{
                padding-right: 20mm;
            }
        .page-section h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .page-section p {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .page-section:last-of-type {
            page-break-after: auto;
        }
    </style>

    <script>
        function printSpecificContent() {
            var printContents = document.getElementById('printable-content').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            document.body.innerHTML = originalContents;
            window.location.reload(); // Reload to restore any lost event listeners
        }
    </script>
</x-filament-panels::page>
