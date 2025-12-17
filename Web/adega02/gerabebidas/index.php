<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gera Bebidas - Controle de Estoque</title>
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --light-bg: #f8fafc;
            --light-card: #ffffff;
            --light-border: #e2e8f0;
            --primary: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --text-dark: #1e293b;
            --text-light: #64748b;
        }

        body {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
        }

        .light-card {
            background: var(--light-card);
            border: 1px solid var(--light-border);
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .light-card:hover {
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .header-card {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border-radius: 15px;
        }

        .metric-card {
            background: var(--light-card);
            border-left: 4px solid var(--primary);
            border-radius: 10px;
        }

        .metric-card.success {
            border-left-color: var(--success);
        }

        .metric-card.warning {
            border-left-color: var(--warning);
        }

        .form-section {
            background: var(--light-card);
            border-radius: 15px;
            padding: 2rem;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid var(--light-border);
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4);
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .metric-label {
            color: var(--text-light);
            font-weight: 500;
        }

        .section-title {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--light-border);
            padding-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="light-card header-card p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">
                                <i class="bi bi-cup-straw me-2"></i>Controle de Estoque - Gera Bebidas
                            </h1>
                            <p class="mb-0 opacity-75">Sistema de gestão de bebidas e controle de vendas</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-light text-dark fs-6">27/11</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Coluna Esquerda - Métricas e Ações -->
            <div class="col-lg-4 mb-4">
                <!-- Ações Rápidas -->
                <div class="light-card p-4 mb-4">
                    <h5 class="section-title">Ações Rápidas</h5>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-lg">
                            <i class="bi bi-cart-plus me-2"></i>Realizar Nova Venda
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="bi bi-gear me-2"></i>Configurações
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="bi bi-graph-up me-2"></i>Relatório de Vendas
                        </button>
                    </div>
                </div>

                <!-- Métricas do Dia -->
                <div class="light-card p-4 mb-4">
                    <h5 class="section-title">Métricas de Hoje</h5>
                    
                    <div class="metric-card p-3 mb-3">
                        <div class="metric-value text-primary">R$ 1.479,88</div>
                        <div class="metric-label">Vendas Brutas</div>
                    </div>

                    <div class="metric-card p-3 mb-3 success">
                        <div class="metric-value text-success">R$ 87,00</div>
                        <div class="metric-label">Caixa Diário (Dinheiro)</div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="metric-card p-3">
                                <div class="metric-value text-warning">R$ 154,00</div>
                                <div class="metric-label small">Vendas</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-card p-3">
                                <div class="metric-value text-info">R$ 67,00</div>
                                <div class="metric-label small">Trocos</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Reposição -->
                <div class="light-card p-4">
                    <h5 class="section-title">Lista de Reposição</h5>
                    <div class="metric-card p-3 mb-3 warning">
                        <div class="metric-value text-warning">12</div>
                        <div class="metric-label">Produtos com Estoque Baixo</div>
                    </div>
                    <button class="btn btn-outline-warning w-100">
                        <i class="bi bi-list-check me-2"></i>Ver Lista Completa
                    </button>
                </div>
            </div>

            <!-- Coluna Direita - Cadastro de Produtos -->
            <div class="col-lg-8">
                <div class="light-card p-4">
                    <h5 class="section-title">
                        <i class="bi bi-plus-circle me-2"></i>Cadastrar Nova Bebida
                    </h5>
                    
                    <form>
                        <div class="row">
                            <!-- Informações Básicas -->
                            <div class="col-md-6">
                                <div class="form-section mb-4">
                                    <h6 class="fw-bold mb-3 text-primary">
                                        <i class="bi bi-info-circle me-2"></i>Informações Básicas
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nome da Bebida</label>
                                        <input type="text" class="form-control" placeholder="Ex: Coca-Cola 2L">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Marca/Fabricante</label>
                                        <input type="text" class="form-control" placeholder="Ex: Coca-Cola">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Tipo</label>
                                        <select class="form-control">
                                            <option value="">Selecione o tipo...</option>
                                            <option value="vinho">Vinho</option>
                                            <option value="cerveja">Cerveja</option>
                                            <option value="refrigerante">Refrigerante</option>
                                            <option value="destilado">Destilado</option>
                                            <option value="energetico">Energético</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Controle de Estoque -->
                            <div class="col-md-6">
                                <div class="form-section mb-4">
                                    <h6 class="fw-bold mb-3 text-success">
                                        <i class="bi bi-box me-2"></i>Controle de Estoque
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Quantidade</label>
                                        <input type="number" class="form-control" placeholder="0" min="0">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Estoque Mínimo</label>
                                        <input type="number" class="form-control" placeholder="5" min="0">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Valor Pago (Custo)</label>
                                        <input type="text" class="form-control" placeholder="R$ 0,00">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Datas Importantes -->
                            <div class="col-md-6">
                                <div class="form-section mb-4">
                                    <h6 class="fw-bold mb-3 text-warning">
                                        <i class="bi bi-calendar me-2"></i>Datas Importantes
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Data da Compra</label>
                                        <input type="date" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Data de Vencimento</label>
                                        <input type="date" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <!-- Preços -->
                            <div class="col-md-6">
                                <div class="form-section mb-4">
                                    <h6 class="fw-bold mb-3 text-info">
                                        <i class="bi bi-currency-dollar me-2"></i>Preços
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Preço de Custo</label>
                                        <input type="text" class="form-control" placeholder="R$ 0,00">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Preço de Venda</label>
                                        <input type="text" class="form-control" placeholder="R$ 0,00">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Margem de Lucro</label>
                                        <input type="text" class="form-control" placeholder="0%" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botão Salvar -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg py-3">
                                        <i class="bi bi-check-circle me-2"></i>SALVAR NO ESTOQUE
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Outras Métricas -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="light-card p-4 text-center">
                            <i class="bi bi-clock-history display-4 text-muted mb-3"></i>
                            <h6 class="fw-bold">Produtos Próximos do Vencimento</h6>
                            <div class="metric-value text-warning">8</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="light-card p-4 text-center">
                            <i class="bi bi-star display-4 text-muted mb-3"></i>
                            <h6 class="fw-bold">Produtos Mais Vendidos</h6>
                            <div class="metric-value text-success">15</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>