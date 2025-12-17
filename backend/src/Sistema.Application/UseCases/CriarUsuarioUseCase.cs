using System;
using System.Collections.Generic;
using System.Text;
using Sistema.Application.DTOs;
using Sistema.Application.Interfaces;
using Sistema.Domain.Entities;

namespace Sistema.Application.UseCases;

public class CriarUsuarioUseCase
{
    private readonly IUsuarioRepository _repo;

    public CriarUsuarioUseCase(IUsuarioRepository repo)
    {
        _repo = repo;
    }

    public async Task<Guid> ExecutarAsync(CriarUsuarioRequest request, CancellationToken ct = default)
    {
        // regra de aplicação (orquestração)
        var email = request.Email.Trim().ToLowerInvariant();

        if (await _repo.EmailJaExisteAsync(email, ct))
            throw new InvalidOperationException("Já existe um usuário com este e-mail.");

        // regra de domínio (valida dentro da entidade)
        var usuario = new Usuario(request.Nome, email);

        await _repo.AdicionarAsync(usuario, ct);

        return usuario.Id;
    }
}