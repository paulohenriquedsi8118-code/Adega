using System;
using System.Collections.Generic;
using System.Text;

namespace Sistema.Domain.Entities;

public class Usuario
{
    public Guid Id { get; private set; }
    public string Nome { get; private set; } = string.Empty;
    public string Email { get; private set; } = string.Empty;
    public bool Ativo { get; private set; } = true;
    public DateTime CriadoEm { get; private set; } = DateTime.UtcNow;

    // Para ORM (EF Core) - não usar direto
    private Usuario() { }

    public Usuario(string nome, string email)
    {
        Id = Guid.NewGuid();
        AlterarNome(nome);
        AlterarEmail(email);
    }

    public void AlterarNome(string nome)
    {
        if (string.IsNullOrWhiteSpace(nome))
            throw new ArgumentException("Nome é obrigatório.", nameof(nome));

        Nome = nome.Trim();
    }

    public void AlterarEmail(string email)
    {
        if (string.IsNullOrWhiteSpace(email))
            throw new ArgumentException("Email é obrigatório.", nameof(email));

        Email = email.Trim().ToLowerInvariant();
    }

    public void Desativar() => Ativo = false;
    public void Ativar() => Ativo = true;
}

