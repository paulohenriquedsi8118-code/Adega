namespace Sistema.Domain.Entities;

public class Produto
{
    public Guid Id { get; private set; } = Guid.NewGuid();
    public string Nome { get; private set; } = string.Empty;
    public string? Marca { get; private set; }
    public string? Tipo { get; private set; } // Vodka, Whisky, Vinho etc
    public decimal PrecoVenda { get; private set; }
    public int EstoqueMinimo { get; private set; } = 0;
    public bool Ativo { get; private set; } = true;
    public DateTime CriadoEm { get; private set; } = DateTime.UtcNow;

    private Produto() { } // EF

    public Produto(string nome, decimal precoVenda, string? marca = null, string? tipo = null, int estoqueMinimo = 0)
    {
        AlterarNome(nome);
        AlterarPrecoVenda(precoVenda);
        Marca = marca;
        Tipo = tipo;
        EstoqueMinimo = estoqueMinimo;
    }

    public void AlterarNome(string nome)
    {
        if (string.IsNullOrWhiteSpace(nome)) throw new ArgumentException("Nome é obrigatório.");
        Nome = nome.Trim();
    }

    public void AlterarPrecoVenda(decimal preco)
    {
        if (preco < 0) throw new ArgumentException("Preço de venda inválido.");
        PrecoVenda = preco;
    }

    public void Desativar() => Ativo = false;
    public void Ativar() => Ativo = true;
}
