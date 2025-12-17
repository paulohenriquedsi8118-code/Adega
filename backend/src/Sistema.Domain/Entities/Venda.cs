using Sistema.Domain.Enums;

namespace Sistema.Domain.Entities;

public class Venda
{
    public Guid Id { get; private set; } = Guid.NewGuid();
    public DateTime Data { get; private set; } = DateTime.UtcNow;

    public FormaPagamento FormaPagamento { get; private set; }
    public decimal ValorTotal { get; private set; }
    public decimal ValorRecebido { get; private set; }
    public decimal Troco { get; private set; }

    public List<VendaItem> Itens { get; private set; } = new();

    private Venda() { } // EF

    public Venda(FormaPagamento formaPagamento, decimal valorRecebido)
    {
        FormaPagamento = formaPagamento;
        ValorRecebido = valorRecebido;
    }

    public void AdicionarItem(Guid produtoId, int quantidade, decimal valorUnitario, decimal custoUnitario)
    {
        if (produtoId == Guid.Empty) throw new ArgumentException("Produto inválido.");
        if (quantidade <= 0) throw new ArgumentException("Quantidade inválida.");
        if (valorUnitario < 0) throw new ArgumentException("Valor unitário inválido.");
        if (custoUnitario < 0) throw new ArgumentException("Custo unitário inválido.");

        Itens.Add(new VendaItem(Id, produtoId, quantidade, valorUnitario, custoUnitario));
        RecalcularTotais();
    }

    private void RecalcularTotais()
    {
        ValorTotal = Itens.Sum(i => i.Quantidade * i.ValorUnitario);
        Troco = ValorRecebido > ValorTotal ? (ValorRecebido - ValorTotal) : 0;
    }
}
