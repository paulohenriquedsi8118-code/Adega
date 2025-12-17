using Microsoft.EntityFrameworkCore;
using Sistema.Domain.Entities;

namespace Sistema.Infrastructure.Persistence;

public class AppDbContext : DbContext
{
    public AppDbContext(DbContextOptions<AppDbContext> options)
        : base(options)
    {
    }

    public DbSet<Produto> Produtos => Set<Produto>();
    public DbSet<EstoqueMovimentacao> EstoqueMovimentacoes => Set<EstoqueMovimentacao>();
    public DbSet<Venda> Vendas => Set<Venda>();
    public DbSet<VendaItem> VendaItens => Set<VendaItem>();
    public DbSet<Usuario> Usuarios => Set<Usuario>();

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        base.OnModelCreating(modelBuilder);

        // Produto
        modelBuilder.Entity<Produto>(entity =>
        {
            entity.ToTable("Produtos");

            entity.HasKey(p => p.Id);

            entity.Property(p => p.Nome)
                .IsRequired()
                .HasMaxLength(200);

            entity.Property(p => p.Marca)
                .HasMaxLength(100);

            entity.Property(p => p.Tipo)
                .HasMaxLength(100);

            entity.Property(p => p.PrecoVenda)
                .HasColumnType("decimal(18,2)");

            entity.Property(p => p.Ativo)
                .IsRequired();

            entity.HasIndex(p => p.Nome);
        });

        // EstoqueMovimentacao
        modelBuilder.Entity<EstoqueMovimentacao>(entity =>
        {
            entity.ToTable("EstoqueMovimentacoes");

            entity.HasKey(e => e.Id);

            entity.Property(e => e.Quantidade)
                .IsRequired();

            entity.Property(e => e.CustoUnitario)
                .HasColumnType("decimal(18,2)");

            entity.Property(e => e.Observacao)
                .HasMaxLength(300);

            entity.HasOne(e => e.Produto)
                .WithMany()
                .HasForeignKey(e => e.ProdutoId);
        });

        // Venda
        modelBuilder.Entity<Venda>(entity =>
        {
            entity.ToTable("Vendas");

            entity.HasKey(v => v.Id);

            entity.Property(v => v.ValorTotal)
                .HasColumnType("decimal(18,2)");

            entity.Property(v => v.ValorRecebido)
                .HasColumnType("decimal(18,2)");

            entity.Property(v => v.Troco)
                .HasColumnType("decimal(18,2)");

            entity.HasMany(v => v.Itens)
                .WithOne(i => i.Venda)
                .HasForeignKey(i => i.VendaId);
        });

        // VendaItem
        modelBuilder.Entity<VendaItem>(entity =>
        {
            entity.ToTable("VendaItens");

            entity.HasKey(i => i.Id);

            entity.Property(i => i.ValorUnitario)
                .HasColumnType("decimal(18,2)");

            entity.Property(i => i.CustoUnitario)
                .HasColumnType("decimal(18,2)");

            entity.HasOne(i => i.Produto)
                .WithMany()
                .HasForeignKey(i => i.ProdutoId);
        });
    }
}
