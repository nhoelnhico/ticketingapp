<?php
// =========================================================
// PHP BACKEND LOGIC FOR TICKET NUMBER MANAGEMENT
// This script handles reading, updating, and resetting the ticket number daily.
// =========================================================

$stateFile = 'ticket_state.txt';
$currentDate = date('Y-m-d'); // Current date in YYYY-MM-DD format

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
        /* Hide buttons and unnecessary elements when printing the ticket */
        @media print {
            body {
                justify-content: flex-start;
                background-color: #fff !important;
            }
            .no-print {
                display: none !important;
            }
            .ticket-card {
                box-shadow: none !important;
                border: 2px dashed #000;
                padding: 1rem;
                max-width: 300px;
                margin: 0 auto;
            }
            .ticket-number-display {
                font-size: 4rem;
                color: #000;
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
        <h2 class="text-xl font-semibold text-gray-600 mb-2">Your Ticket Number Is:</h2>
        
        <!-- The dynamically generated and formatted ticket number -->
        <div id="ticketNumber" class="ticket-number-display select-none">
            <?php echo htmlspecialchars($displayNumber); ?>
        </div>

        <p class="text-sm text-gray-500 mb-4">
            Today's Date: <?php echo date('F j, Y'); ?>
        </p>

        <!-- Button to generate the next number (reloads the page, triggering the PHP logic) -->
        <!-- NOTE: This button generates the *next* number and should be pressed by staff or upon customer request for a new ticket. -->
        <a href="index.php" class="no-print w-full btn btn-lg text-white font-bold py-3 mb-3 rounded-xl transition duration-150" 
           style="background-color: #d15a77; border-color: #d15a77;">
            Next Ticket (Generates <?php echo sprintf('%02d', $ticketNumber + 1); ?>)
        </a>

        <!-- Button for printing -->
        <button onclick="printTicket()" class="no-print w-full btn btn-lg btn-outline-secondary font-bold py-3 rounded-xl transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block w-5 h-5 me-2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Ticket
        </button>

        <small class="no-print mt-3 block text-gray-400">Current stored ticket number: <?php echo htmlspecialchars($displayNumber); ?></small>
    </div>

    <script>
        /**
         * The print function uses the standard browser printing mechanism.
         * The operating system (Windows, macOS, Android, etc.) handles connecting to the printer,
         * including Bluetooth and portable receipt printers.
         */
        function printTicket() {
            // This triggers the native browser print dialog
            window.print();
        }
    </script>
    <!-- Bootstrap JS (optional, for button effects) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>