<?php
namespace App\Filament\Resources\OrderResource\Pages;

use Mpdf\Mpdf;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\View;
use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class DetailsOrder extends Page
{
    protected static string $resource = OrderResource::class;
    protected static string $view = 'filament.resources.order-resource.pages.details-order';

    use InteractsWithRecord;

    public $items;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->items = $this->record->items;
    }
    public function generatePdf()
    {
        // Prepare data to pass to the Blade view
        $data = [
            'items' => $this->items,
        ];

        // Render the HTML content from a Blade view
        $html = View::make('pdf.download', $data)->render();

        // Create an instance of Mpdf with optional settings
        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        // Write the HTML content to the PDF
        $mpdf->WriteHTML($html);

        // Output the generated PDF to the browser for download
        return response()->streamDownload(
            fn () => $mpdf->Output(),
            'forms.pdf'
        );
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Back')
                ->icon('heroicon-o-arrow-left')
                ->label('Back')
                ->color('danger')
                ->button()
                ->url('/admin/orders'),
            Actions\Action::make('downloadPdf')
                ->label('Download PDF')
                ->color('info')
                ->icon('heroicon-o-arrow-down-tray')
                ->action('generatePdf'),
        ];
    }
}
