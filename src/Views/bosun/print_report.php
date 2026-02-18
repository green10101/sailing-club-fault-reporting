<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Faults Work Report</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 15mm 10mm;
            }
            .no-print {
                display: none !important;
            }
            body {
                font-size: 11pt;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            max-width: 210mm;
            margin: 0 auto;
            padding: 10px;
            background: white;
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .report-header h1 {
            margin: 0 0 10px 0;
            font-size: 24pt;
        }
        
        .report-meta {
            font-size: 10pt;
            color: #666;
        }
        
        .boat-section {
            page-break-inside: avoid;
            margin-bottom: 25px;
        }
        
        .boat-header {
            background-color: #004d99;
            color: white;
            padding: 8px 12px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 14pt;
        }
        
        .boat-info {
            font-size: 10pt;
            margin-left: 12px;
            margin-bottom: 10px;
        }
        
        .fault-card {
            page-break-inside: avoid;
            border: 1px solid #333;
            margin-bottom: 15px;
            padding: 10px;
            background: #f9f9f9;
        }
        
        .fault-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #666;
            padding-bottom: 5px;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .fault-id {
            font-size: 10pt;
            color: #666;
        }
        
        .fault-status {
            font-size: 10pt;
            padding: 0;
            border-radius: 0;
        }
        
        .status-new { 
            background: transparent; 
            color: inherit; 
            border-left: none;
        }
        .status-new::before {
            content: "🔴 ";
        }
        
        .status-inprogress { 
            background: transparent; 
            color: inherit; 
            border-left: none;
        }
        .status-inprogress::before {
            content: "🟠 ";
        }
        
        .status-waitingparts { 
            background: transparent; 
            color: inherit; 
            border-left: none;
        }
        .status-waitingparts::before {
            content: "🔵 ";
        }
        
        .status-complete {
            background: transparent;
            color: inherit;
            border-left: none;
        }
        .status-complete::before {
            content: "✅ ";
        }
        
        .fault-description {
            margin: 8px 0;
            font-weight: bold;
        }
        
        .fault-meta {
            font-size: 9pt;
            color: #666;
            margin-bottom: 8px;
        }
        
        .fault-details {
            font-size: 10pt;
            margin: 5px 0;
        }
        
        .notes-section {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #999;
        }
        
        .notes-label {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 5px;
        }
        
        .notes-lines {
            border: 1px solid #ccc;
            min-height: 60px;
            padding: 5px;
            background: white;
            line-height: 20px;
            background-image: repeating-linear-gradient(
                white,
                white 19px,
                #ddd 19px,
                #ddd 20px
            );
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #004d99;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14pt;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        
        .print-button:hover {
            background: #003d7a;
        }
        
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 12px 24px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14pt;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        
        .back-button:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <a href="index.php?route=/bosun/dashboard" class="back-button no-print">← Back</a>
    <button onclick="window.print()" class="print-button no-print">🖨️ Print Report</button>
    
    <div class="report-header">
        <h1>Active Faults Work Report</h1>
        <div class="report-meta">
            Generated: <?php echo date('l, F j, Y \a\t g:i A'); ?><br>
            Total Assets with Faults: <?php echo count($boatGroups); ?> | 
            Total Active Faults: <?php echo $totalFaults; ?>
        </div>
    </div>
    
    <?php foreach ($boatGroups as $boatName => $boatData): ?>
        <div class="boat-section">
            <div class="boat-header">
                <?php echo htmlspecialchars($boatName); ?>
            </div>
            <div class="boat-info">
                <strong>Type:</strong> <?php echo htmlspecialchars($boatData['boat_type']); ?> | 
                <strong>Active Faults:</strong> <?php echo count($boatData['faults']); ?>
            </div>
            
            <?php foreach ($boatData['faults'] as $fault): ?>
                <div class="fault-card">
                    <div class="fault-header">
                        <span class="fault-id">Fault #<?php echo htmlspecialchars($fault['id']); ?></span>
                        <?php 
                            $statusClass = 'status-new';
                            if ($fault['status'] === 'In progress') {
                                $statusClass = 'status-inprogress';
                            } elseif ($fault['status'] === 'Waiting parts') {
                                $statusClass = 'status-waitingparts';
                            } elseif ($fault['status'] === 'Complete') {
                                $statusClass = 'status-complete';
                            }
                        ?>
                        <span class="fault-status <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($fault['status']); ?>
                        </span>
                    </div>
                    
                    <div class="fault-description">
                        <strong>Fault:</strong> <?php echo htmlspecialchars($fault['fault_description']); ?>
                    </div>
                    
                    <div class="fault-meta">
                        Reported by: <?php echo htmlspecialchars($fault['reporter_name'] ?? 'Unknown'); ?> 
                        on <?php echo date('d/m/Y', strtotime($fault['reported_at'])); ?>
                    </div>
                    
                    <?php if (!empty($fault['bosun_notes'])): ?>
                        <div class="fault-details">
                            <strong>Bosun Notes:</strong> <?php echo nl2br(htmlspecialchars($fault['bosun_notes'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($fault['bosun_assessment'])): ?>
                        <div class="fault-details">
                            <strong>Assessment:</strong> <?php echo nl2br(htmlspecialchars($fault['bosun_assessment'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($fault['part_required'])): ?>
                        <div class="fault-details">
                            <strong>Part Required:</strong> <?php echo htmlspecialchars($fault['part_required']); ?>
                            <?php if (!empty($fault['part_status'])): ?>
                                | <strong>Status:</strong> <?php echo htmlspecialchars($fault['part_status']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="notes-section">
                        <div class="notes-label">Work Party Notes:</div>
                        <div class="notes-lines">&nbsp;</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($boatGroups)): ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <h2>No Active Faults</h2>
            <p>There are currently no active faults to report.</p>
        </div>
    <?php endif; ?>
</body>
</html>
