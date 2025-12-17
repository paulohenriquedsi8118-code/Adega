using Sistema.Domain.Enums;

namespace Sistema.Domain.Entities;

public class EstoqueMovimentacao
{
    public Guid Id { get; private set; } = Guid.NewGuid();
    public Guid ProdutoId { get; private set; }
    public Produto? Produto { get; private set; }

    public TipoMovimentacaoEstoque Tipo { get; private set; }
    public int Quantidade { get; private set; }
    public decimal? CustoUnitario { get; private set; } // usado em Entrada (para lucro/CMV)
    public string? Observacao { get; private set; }
    public DateTime Data { get; private set; } = DateTime.UtcNow;

    private EstoqueMovimentacao() { } // EF

    public EstoqueMovimentacao(Guid produtoId, TipoMovimentacaoEstoque tipo, int quantidade, decimal? custoUnitario = null, string? observacao = null)
    {
        if (produtoId == Guid.Empty) throw new ArgumentException("Produto inválido.");
        if (quantidade <= 0) throw new ArgumentException("Quantidade inválida.");

        ProdutoId = produtoId;
        Tipo = tipo;
        Quantidade = quantidade;
        CustoUnitario = custoUnitario;
        Observacao = observacao;
    }
}
