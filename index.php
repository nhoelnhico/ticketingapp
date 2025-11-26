<?php
// =========================================================
// PHP BACKEND LOGIC FOR TICKET NUMBER MANAGEMENT
// This script handles reading, updating, and resetting the ticket number daily.
// =========================================================

$stateFile = 'ticket_state.txt';
$currentDate = date('Y-m-d'); // Current date in YYYY-MM-DD format
$currentTime = date('h:i:s A'); // Current time for the receipt timestamp

// Default state if the file doesn't exist or is corrupt
$ticketNumber = 0;
$lastResetDate = $currentDate;

// --- 1. Read the current state ---
if (file_exists($stateFile)) {
    // Read the contents of the file
    $data = file_get_contents($stateFile);
    // Split the data (format: date|number)
    list($storedDate, $storedNumber) = explode('|', $data);

    // Check if the stored date is NOT the current date (daily reset check)
    if ($storedDate !== $currentDate) {
        // --- 2. Daily Reset Action ---
        $ticketNumber = 1; // Start from 1 for the new day
        $lastResetDate = $currentDate;
    } else {
        // --- 3. Increment Action (Same Day) ---
        // Use the stored number and increment it for the next ticket
        $ticketNumber = (int)$storedNumber + 1;
        $lastResetDate = $storedDate;
    }
} else {
    // --- 4. First Time Setup ---
    $ticketNumber = 1; // Start at 1 if the file doesn't exist
}

// --- 5. Save the new state back to the file (critical for persistence) ---
$newState = "{$currentDate}|{$ticketNumber}";
file_put_contents($stateFile, $newState);

// --- 6. Format the number for display (01, 02, etc.) ---
$displayNumber = sprintf('%02d', $ticketNumber);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Title: Grwm Cosmetics -->
    <title>Grwm Cosmetics - Ticket Kiosk</title>
    <!-- Bootstrap CSS CDN for clean styling and responsiveness -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Styles for aesthetics and print */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .ticket-card {
            background-color: #ffffff;
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            width: 90%;
            padding: 2.5rem;
            text-align: center;
        }
        .ticket-number-display {
            font-size: 7rem;
            font-weight: 800;
            color: #d15a77; /* A cosmetic/chroma-themed color */
            line-height: 1;
            margin: 1.5rem 0;
            /* Style specifically for printing */
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        /* Class to hide elements on screen but show them in print */
        .print-only {
            display: none;
        }

        /* Hide buttons and unnecessary elements when printing the ticket */
        @media print {
            /* Set the page size for a standard 80mm thermal receipt */
            @page {
                size: 80mm auto; /* Set paper width to 80mm, height auto */
                margin: 0; /* Remove all default print margins */
            }
            body {
                width: 80mm; /* Force body width to match the paper */
                padding: 0;
                margin: 0;
                justify-content: flex-start;
                background-color: #fff !important;
            }
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
                text-align: center !important; 
            }
            /* Style the ticket card to look like a receipt strip */
            .ticket-card {
                box-shadow: none !important;
                border: none; /* No border for the receipt */
                width: 100%; /* Take full 80mm width */
                max-width: none;
                padding: 0.5rem 0.2rem; /* Minimal internal padding */
                margin: 0;
                font-size: 10pt; /* Smaller base font for receipt print */
            }
            .ticket-number-display {
                font-size: 50pt; /* Huge font for the number on the receipt */
                font-weight: 900;
                color: #000;
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body class="p-4">

    <header class="no-print mb-4 w-full max-w-lg">
        <h1 class="text-3xl font-extrabold text-gray-800 text-center">Chromaesthetics Inc</h1>
        <p class="text-center text-gray-500 text-lg">GRWM Cosmetics Queue System</p>
    </header>

    <div class="ticket-card">
        
        <!-- PRINT ONLY Header -->
        <div class="print-only text-xs font-bold mb-2 pt-2">
            *** Chromaesthetics Inc ***<br>
            GRWM Cosmetics
        </div>

        <h2 class="text-xl font-semibold text-gray-600 mb-2 no-print">Current Ticket to Issue:</h2>
        
        <!-- The dynamically generated and formatted ticket number -->
        <div id="ticketNumber" class="ticket-number-display select-none">
            <?php echo htmlspecialchars($displayNumber); ?>
        </div>
        
        <!-- Print-only instructions for the ticket recipient -->
        <div class="print-only text-sm font-semibold mb-2">
            YOUR QUEUE NUMBER
        </div>

        <!-- Date/Time displayed on screen (non-print) -->
        <p class="text-sm text-gray-500 mb-4 no-print">
            Today's Date: <?php echo date('F j, Y'); ?>
        </p>

        <!-- Time and Date for Print -->
        <div class="print-only text-center text-xs mb-3 border-t border-b border-gray-400 py-1">
            Date: <?php echo date('Y/m/d'); ?> | Time: <?php echo htmlspecialchars($currentTime); ?>
        </div>

        <!-- Consolidated Button: This prints the current number, then reloads the page to generate the next one. -->
        <button onclick="printAndIssueNext()" class="no-print w-full btn btn-lg text-white font-bold py-3 rounded-xl transition duration-150" 
           style="background-color: #d15a77; border-color: #d15a77;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block w-5 h-5 me-2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print & Issue Next Ticket
        </button>

        <small class="no-print mt-3 block text-gray-400">
            Clicking this will print ticket **<?php echo htmlspecialchars($displayNumber); ?>** and then prepare ticket **<?php echo sprintf('%02d', $ticketNumber + 1); ?>**.
        </small>
        
        <!-- PRINT ONLY Footer -->
        <div class="print-only text-center text-xs mt-3 pb-2">
            *** Thank you for waiting! ***
        </div>

    </div>

    <script>
        /**
         * The combined print and issue function.
         * 1. Triggers the print dialog for the CURRENTLY displayed number.
         * 2. After a short delay, reloads the page, which triggers the PHP script
         * to calculate and display the next number.
         */
        function printAndIssueNext() {
            // 1. Trigger the native browser print dialog for the current ticket
            window.print();

            // 2. Delay the reload slightly (500ms) to ensure the print dialog opens before the page changes.
            // This reload triggers the PHP code to increment the number for the next client.
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 500);
        }
    </script>
    <!-- Bootstrap JS (optional, for button effects) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>