<?php

require("init.php");

use WHMCS\Billing\Invoice;
use WHMCS\Billing\Currency;
use WHMCS\Billing\Invoice\Item as InvoiceItem;

class InvoiceUpdater
{
    private $currencyConfig;
    private $domainTLD;
    private $description;

    public function __construct(array $currencyConfig, string $domainTLD, string $description)
    {
        $this->currencyConfig = $currencyConfig;
        $this->domainTLD = $domainTLD;
        $this->description = $description;
    }

    public function run($limit = 500, $updateItems = true)
    {
        try {
            $unpaidInvoices = $this->getUnpaidInvoices($limit);

            foreach ($unpaidInvoices as $invoice) {
                $this->processInvoice($invoice, $updateItems);
            }

            return 'Domain prices updated successfully';
        } catch (Exception $e) {
            return 'An error occurred: ' . $e->getMessage();
        }
    }

    private function getUnpaidInvoices($limit = null)
    {
        $query = Invoice::where('status', 'Unpaid')
            ->where('notes', '!=', 'added')
            ->with(['client']);

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    private function processInvoice(Invoice $invoice, $updateItems)
    {
        $clientCurrencyCode = $invoice->client->currency;
        $clientCurrency = $this->getClientCurrency($clientCurrencyCode);

        if ($clientCurrency && array_key_exists($clientCurrency->code, $this->currencyConfig)) {

            $amountConfig = $this->currencyConfig[$clientCurrency->code];
            $domainInvoiceItems = $this->filterDomainItems($invoice->items, $this->domainTLD);

            if (!$domainInvoiceItems->isEmpty()) {
                $totalPriceIncrease = $updateItems
                    ? $amountConfig['amount']
                    : $this->calculateTotalPriceIncrease($amountConfig['amount'], count($domainInvoiceItems));

                $this->updateInvoiceAndItems($invoice, $domainInvoiceItems, $totalPriceIncrease, $updateItems);
            }
        }
    }

    private function getClientCurrency($currencyCode)
    {
        return Currency::find($currencyCode);
    }

    private function filterDomainItems($items, $domainTLD)
    {
        return $items->filter(function ($item) use ($domainTLD) {
            return $item->type === 'Domain' && strpos($item->description, '.' . $domainTLD) !== false;
        });
    }

    private function calculateTotalPriceIncrease($increaseAmount, $itemCount)
    {
        return $increaseAmount * $itemCount;
    }

    private function updateInvoiceAndItems(Invoice $invoice, $domainInvoiceItems, $totalPriceIncrease, $updateItems)
    {
        $invoice->notes = 'added';
        $invoice->subtotal += $totalPriceIncrease;
        $invoice->total += $totalPriceIncrease;
        $invoice->save();

        if (!$updateItems) {
            $newItem = new InvoiceItem([
                'description' => $this->description,
                'amount' => $totalPriceIncrease,
            ]);
            $invoice->items()->save($newItem);
        } else {
            foreach ($domainInvoiceItems as $item) {
                $item->amount += $totalPriceIncrease;
                $item->save();
            }
        }
    }
}

// Configuration
$currencyConfig = [
    'USD' => ['amount' => 50],
    'BDT' => ['amount' => 500],
    // Add more currencies and amounts here
];

$domainTLD = 'com'; // Cheange Com Domain
$description = 'Domain Price Increase';


// Script Behavior
$limit = 100;       // Set the limit for the number of invoices to process
$updateItems = true; // Set to true to update items, false to add new items



// Initialize the process
$updateUnpaidInvoice = new InvoiceUpdater($currencyConfig, $domainTLD, $description);
$updateUnpaidInvoice->run($limit, $updateItems); //Adjust the limit and toggle to true in order to update items without introducing new additions.