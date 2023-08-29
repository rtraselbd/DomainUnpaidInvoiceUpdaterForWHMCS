# Domain Unpaid Invoice Updater for WHMCS

The Domain Unpaid Invoice Updater for WHMCS is a simple PHP script designed to update domain prices on unpaid invoices within a WHMCS billing system. With just a few steps, you can customize and run the script to apply price increases to specific top-level domains (TLDs) based on client currency, while also controlling the number of invoices processed and whether items are updated or added.

## Usage

1. **Download the Script**: Click the "Download Code" button on the GitHub repository page to download the `unpaid_invoice_updater.php` file to your computer.

2. **Configure the Script**: Open the downloaded `unpaid_invoice_updater.php` file in a text editor. You'll find a configuration section in the script where you can adjust the price increase for different currencies, specify the target TLD, and set parameters for script behavior:

    ```php
    // Configuration
    $currencyConfig = [
        'USD' => ['amount' => 50],
        'BDT' => ['amount' => 500],
        // Add more currencies and amounts here
    ];
    $domainTLD = 'com'; // Change Com Domain
    $description = 'Domain Price Increase';
    
    // Script Behavior
    $limit = 100;       // Set the limit for the number of invoices to process
    $updateItems = true; // Set to true to update items, false to add new items
    ```

3. **Upload to Your Server**: Upload the edited `invoice_updater.php` file to your server, preferably in the root directory of your WHMCS installation.

4. **Access via Browser**: Open your web browser and navigate to the location where you uploaded the script, e.g., `http://yourdomain.com/invoice_updater.php`.

5. **Run the Script**: The script will run when accessed through the browser with the configured parameters. You might see a "success" or "error" message based on the outcome.

## Important Notes

- This script assumes you have a working WHMCS installation and appropriate permissions to access the necessary database tables.
- Before running the script on a production system, it's recommended to test it on a staging environment to ensure it works as expected.

## Contributing

Feel free to contribute to this project by reporting issues or suggesting improvements through GitHub Issues and Pull Requests.