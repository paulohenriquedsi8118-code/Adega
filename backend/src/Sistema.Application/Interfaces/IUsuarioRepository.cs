using System;
using System.Collections.Generic;
using System.Text;
using Sistema.Domain.Entities;

namespace Sistema.Application.Interfaces;

public interface IUsuarioRepository
{
    Task AdicionarAsync(Usuario usuario, CancellationToken ct = default);
    Task<bool> EmailJaExisteAsync(string email, CancellationToken ct = default);
}
