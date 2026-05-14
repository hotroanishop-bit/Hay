<?php
/**
 * Invoice Controller
 * Handles invoice and receipt display for printing
 */

class InvoiceController extends BaseController
{
    private InvoiceService $invoiceService;

    public function __construct()
    {
        $this->invoiceService = new InvoiceService();
    }

    /**
     * Display deposit invoice for printing
     *
     * @param int $id Deposit ID
     * @return void
     */
    public function depositInvoice(int $id): void
    {
        $userId = $_SESSION['user']['id'] ?? null;
        
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        $invoice = $this->invoiceService->generateDepositInvoice($id, $userId);
        
        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found or not available.');
            $this->redirect('/billing/history');
            return;
        }

        $this->renderPrintView('templates/invoice', [
            'pageTitle' => 'Invoice ' . $invoice['invoice_number'],
            'invoice' => $invoice,
        ]);
    }

    /**
     * Display purchase receipt for printing
     *
     * @param int $id Transaction ID
     * @return void
     */
    public function purchaseReceipt(int $id): void
    {
        $userId = $_SESSION['user']['id'] ?? null;
        
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        $receipt = $this->invoiceService->generatePurchaseReceipt($id, $userId);
        
        if (!$receipt) {
            $this->setFlash('error', 'Receipt not found or not available.');
            $this->redirect('/billing/history');
            return;
        }

        $this->renderPrintView('templates/receipt', [
            'pageTitle' => 'Receipt ' . $receipt['invoice_number'],
            'receipt' => $receipt,
        ]);
    }

    /**
     * Render a view using the print layout (minimal, no sidebar/header)
     *
     * @param string $view Path to the view
     * @param array $data Data to pass to the view
     * @return void
     */
    protected function renderPrintView(string $view, array $data = []): void
    {
        extract($data);
        
        $contentView = VIEWS_PATH . '/' . $view . '.php';
        
        require VIEWS_PATH . '/layouts/print_layout.php';
    }
}
