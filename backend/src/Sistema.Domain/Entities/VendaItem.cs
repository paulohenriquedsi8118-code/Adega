namespace Sistema.Domain.Entities;

public class VendaItem
{
    public Guid Id { get; private set; } = Guid.NewGuid();
    public Guid VendaId { get; private set; }
    public Venda? Venda { get; private set; }

    public Guid ProdutoId { get; private set; }
    public Produto? Produto { get; private set; }

    public int Quantidade { get; private set; }
    public decimal ValorUnitario { get; private set; }
    public decimal CustoUnitario { get; private set; } // para calcular lucro/CMV

    private VendaItem() { } // EF

    public VendaItem(Guid vendaId, Guid produtoId, int quantidade, decimal valorUnitario, decimal custoUnitario)
    {
        VendaId = vendaId;
        ProdutoId = produtoId;
        Quantidade = quantidade;
        ValorUnitario = valorUnitario;
        CustoUnitario = custoUnitario;
    }
}
