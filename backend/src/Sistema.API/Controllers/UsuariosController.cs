using Microsoft.AspNetCore.Mvc;
using Sistema.Application.DTOs;
using Sistema.Application.UseCases;

namespace Sistema.API.Controllers;

[ApiController]
[Route("api/[controller]")]
public class UsuariosController : ControllerBase
{
    private readonly CriarUsuarioUseCase _useCase;

    public UsuariosController(CriarUsuarioUseCase useCase)
    {
        _useCase = useCase;
    }

    [HttpPost]
    public async Task<IActionResult> Criar([FromBody] CriarUsuarioRequest request, CancellationToken ct)
    {
        var id = await _useCase.ExecutarAsync(request, ct);
        return CreatedAtAction(nameof(Criar), new { id }, new { id });
    }
}
