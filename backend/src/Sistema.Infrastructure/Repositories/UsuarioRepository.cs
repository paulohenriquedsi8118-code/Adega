using System;
using System.Collections.Generic;
using System.Text;
using Microsoft.EntityFrameworkCore;
using Sistema.Application.Interfaces;
using Sistema.Domain.Entities;
using Sistema.Infrastructure.Persistence;

namespace Sistema.Infrastructure.Repositories;

public class UsuarioRepository : IUsuarioRepository
{
    private readonly AppDbContext _context;

    public UsuarioRepository(AppDbContext context)
    {
        _context = context;
    }

    public async Task AdicionarAsync(Usuario usuario, CancellationToken ct = default)
    {
        await _context.Usuarios.AddAsync(usuario, ct);
        await _context.SaveChangesAsync(ct);
    }

    public async Task<bool> EmailJaExisteAsync(string email, CancellationToken ct = default)
    {
        return await _context.Usuarios
            .AnyAsync(u => u.Email == email, ct);
    }
}