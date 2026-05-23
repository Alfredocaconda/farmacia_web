<?php

namespace App\Http\Controllers;

use App\Models\empresa;
use Illuminate\Http\Request;




class EmpresaController extends Controller
{
    private $caminho = 'empresa.txt';
    /**
     * Display a listing of the resource.
     */
    private function getPath()
    {
        return storage_path('app/' . $this->caminho);
    }

    // READ
    public function index()
    {
        if (!file_exists($this->getPath())) {
            return view('pages.admin.empresa', ['empresa' => null]);
        }

        $conteudo = file_get_contents($this->getPath());
        $empresa = json_decode($conteudo);

        return view('pages.admin.empresa', compact('empresa'));
    }

    // CREATE / UPDATE
    public function salvar(Request $request)
    {
        $dados = $request->except('logo');

        // Upload da logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $nome = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $nome);

            $dados['logo'] = 'uploads/' . $nome;
        } else {
            // mantém a antiga
            if (file_exists($this->getPath())) {
                $antigo = json_decode(file_get_contents($this->getPath()), true);
                $dados['logo'] = $antigo['logo'] ?? null;
            }
        }

        file_put_contents($this->getPath(), json_encode($dados, JSON_PRETTY_PRINT));

        return redirect()->back()->with('success', 'Dados da empresa salvos!');
    }

    // DELETE
    public function deletar()
    {
        if (file_exists($this->getPath())) {
            unlink($this->getPath());
        }

        return redirect()->back()->with('success', 'Dados removidos!');
    }
}