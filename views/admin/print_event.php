<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório do Evento #<?php echo $event['id']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.5; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #000; margin-bottom: 10px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px; }
        .section { margin-bottom: 30px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .info-item { margin-bottom: 10px; }
        .label { font-weight: bold; display: block; font-size: 0.9em; color: #666; }
        .value { font-size: 1.1em; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
        th { background-color: #f0f0f0; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.9em; font-weight: bold; border: 1px solid #999; }
        .highlight { background-color: #fff3cd; }
        .footer { margin-top: 50px; font-size: 0.8em; text-align: center; color: #666; border-top: 1px solid #ccc; padding-top: 10px; }
        
        @media print {
            body { max-width: 100%; padding: 0; }
            .no-print { display: none !important; }
            .section { page-break-inside: avoid; }
        }
        
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: right;">
        <button onclick="window.print()" class="btn">🖨️ Imprimir / Salvar PDF</button>
    </div>

    <div class="header">
        <h1>Relatório de Evento</h1>
        <p>Documento gerado em <?php echo date('d/m/Y H:i'); ?></p>
    </div>

    <div class="section">
        <h2>Dados do Evento</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">ID / Título:</span>
                <span class="value">#<?php echo $event['id']; ?> - <?php echo htmlspecialchars($event['name']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Status:</span>
                <span class="value"><?php echo $event['status']; ?></span>
            </div>
            <div class="info-item">
                <span class="label">Responsável pelo Evento:</span>
                <span class="value"><?php echo htmlspecialchars($event['creator_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Localização:</span>
                <span class="value"><?php echo htmlspecialchars($event['location_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Horário Início:</span>
                <span class="value"><?php echo !empty($event['start_time']) ? date('H:i', strtotime($event['start_time'])) : '--:--'; ?></span>
            </div>
            <div class="info-item">
                <span class="label">Horário Término:</span>
                <span class="value"><?php echo !empty($event['end_time']) ? date('H:i', strtotime($event['end_time'])) : '--:--'; ?></span>
            </div>
        </div>
        <div class="info-item" style="margin-top: 15px;">
            <span class="label">Descrição:</span>
            <div class="value" style="font-size: 1em; white-space: pre-line; background: #f9f9f9; padding: 10px; border: 1px solid #eee;"><?php echo htmlspecialchars($event['description']); ?></div>
        </div>
    </div>

    <div class="section">
        <h2>Equipamentos e Materiais</h2>
        
        <?php if ($isFinished): ?>
            <p><strong>Filtro:</strong> Exibindo itens não devolvidos, pendências ou histórico relevante.</p>
        <?php else: ?>
            <p><strong>Checklist:</strong> Utilize esta lista para conferência de entrega dos materiais.</p>
        <?php endif; ?>

        <?php if (empty($loans)): ?>
            <p><em>Nenhum equipamento foi reservado para este evento.</em></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">Check</th>
                        <th>Item</th>
                        <th style="text-align: center;">Qtd Solicitada</th>
                        <th style="text-align: center;">Qtd Conferida</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Aggregate loans by Asset ID
                    $aggregated = [];
                    foreach ($loans as $loan) {
                        $id = $loan['asset_id'];
                        if (!isset($aggregated[$id])) {
                            $aggregated[$id] = [
                                'name' => $loan['asset_name'],
                                'qty' => 0,
                                'statuses' => [],
                                'requires_patrimony' => false // Will resolve below
                            ];
                        }
                        $aggregated[$id]['qty']++;
                        $aggregated[$id]['statuses'][] = $loan['status'];
                    }
                    
                    // Populate requires_patrimony
                    // We need a way to check. Instantiating model here for simplicity or update controller.
                    // Doing inline for minimal intrusion if controller not updated yet.
                    if (isset($assetModel)) { // It is instantiated in controller
                        foreach ($aggregated as $aid => &$data) {
                            $a = $assetModel->getAssetById($aid);
                            if ($a) {
                                $data['requires_patrimony'] = !empty($a['requires_patrimony']);
                            }
                        }
                    }

                    foreach ($aggregated as $assetId => $item): 
                        // Determine overall status description
                        $uniqueStatuses = array_unique($item['statuses']);
                        $statusDisplay = implode(', ', $uniqueStatuses);
                        
                        // Highlight if any item is not returned (for finished events)
                        $isPending = in_array('Emprestado', $item['statuses']) || in_array('Atrasado', $item['statuses']);
                        $rowClass = ($isFinished && $isPending) ? 'highlight' : '';
                    ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td style="text-align: center;"><input type="checkbox"></td>
                            <td>
                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                <?php if (!empty($item['requires_patrimony'])): ?>
                                    <div class="mt-2 text-muted small">
                                        Identificação Patrimonial:
                                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 5px;">
                                            <?php for($i=0; $i<$item['qty']; $i++): ?>
                                                <div style="flex: 0 0 auto;">
                                                    <span style="display: block; font-size: 0.8em; margin-bottom: 2px;">Item #<?php echo $i+1; ?></span>
                                                    <input type="text" style="width: 140px; padding: 4px; border: 1px solid #999; border-radius: 3px;" placeholder="Patrimônio">
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; font-weight: bold;"><?php echo $item['qty']; ?></td>
                            <td style="text-align: center; color: #999;">___ / <?php echo $item['qty']; ?></td>
                            <td>
                                <span class="status-badge">
                                    <?php echo htmlspecialchars($statusDisplay); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($eventPendingItems)): ?>
    <div class="section">
        <h2 style="color: #d9534f;">⚠️ Pendências e Ocorrências</h2>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Nota do Usuário</th>
                    <th>Situação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($eventPendingItems as $item): ?>
                <tr>
                    <td><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo htmlspecialchars($item['user_note'] ?? '-'); ?></td>
                    <td>
                        <strong><?php echo strtoupper($item['status']); ?></strong>
                        <?php if ($item['observation']): ?>
                            <br><small><?php echo htmlspecialchars($item['observation']); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="footer">
        <p>Documento de conferência interna. Assinatura do Responsável: _________________________________________________</p>
    </div>

    <script>
        // Optional: Auto print if intended
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
