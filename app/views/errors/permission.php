<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Required - CerneCMS</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background-color: #F9FAFB;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 1rem;
        }

        .card {
            max-width: 600px;
            width: 100%;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        h2 {
            color: #991B1B;
            margin-top: 0;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        p {
            color: #1F2937;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .details {
            background: white;
            padding: 1rem;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            margin: 1.5rem 0;
            font-size: 0.9rem;
        }

        .details-row {
            margin-bottom: 0.5rem;
            color: #4B5563;
        }

        .details-row:last-child {
            margin-bottom: 0;
        }

        .error-text {
            color: #DC2626;
        }

        .command-block {
            background: #1F2937;
            color: #F9FAFB;
            padding: 1rem;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .label {
            font-weight: 500;
            color: #374151;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2>Setup Required</h2>
        <p>The CMS needs permission to write to the cache directory to function correctly.</p>

        <div class="details">
            <div class="details-row">
                <strong>Target:</strong>
                <?= htmlspecialchars($path) ?>
            </div>
            <div class="details-row error-text">
                <strong>Error:</strong>
                <?= htmlspecialchars($error) ?>
            </div>
        </div>

        <p class="label">To fix this, run these commands on your server:</p>
        <pre class="command-block">mkdir -p <?= htmlspecialchars($path) ?>
chmod -R 777 <?= htmlspecialchars($path) ?></pre>
    </div>
</body>

</html>