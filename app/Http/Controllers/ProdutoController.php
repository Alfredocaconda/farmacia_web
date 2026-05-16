<?php

namespace App\Http\Controllers;

use App\Models\categoria;
use App\Models\produto;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $valor = Produto::with(['categoria', 'funcionario'])
            ->orderBy('nome', 'desc')
            ->get();

        $categorias = Categoria::orderBy('nome', 'asc')->get();

        return view('pages.admin.produto', compact('valor', 'categorias'));
    }  
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        try {
                //DEFINIR REGRAS DE VALIDAÇÃO
                $rules = [
                'nome' => ['required', 'string', 'regex:/^[a-zA-ZÀ-ÿ\s]+$/'],
                'descricao' => ['required'],
            ];

            $request->validate($rules, [
                'nome.required' => 'O NOME É OBRIGATÓRIO!',
                'nome.regex' => 'O NOME DEVE CONTER APENAS LETRAS!',
                'descricao.required' => 'A DESCRIÇÃO É OBRIGATÓRIA!',
            ]);

            // Verificar se já existe produto igual
            $produtoDuplicado = produto::where('nome', $request->nome)
                ->where('descricao', $request->descricao)
                ->where('categoria_id', $request->categoria_id);

            if ($request->filled('id')) {
                $produtoDuplicado->where('id', '!=', $request->id);
            }

            if ($produtoDuplicado->exists()) {
                return redirect()->back()->withInput()->with("ERRO", "JÁ EXISTE UM PRODUTO COM O MESMO NOME, DESCRIÇÃO E CATEGORIA.");
            }

            $valor = $request->filled('id') ? produto::find($request->id) : new produto();

            //PREENCHER OS CAMPOS DO FORMULARIO
            $valor->nome=$request->nome;
            $valor->descricao=$request->descricao;
            $valor->categoria_id=$request->categoria_id;
            $valor->id_funcionario = Auth::guard('funcionario')->user()->id;
            $valor->save();
            return redirect()->back()->with("SUCESSO",$request->filled('id') ? "PRODUTO ACTUALIZADO COM SUCESSO" : "PRODUTO CADASTRADO COM SUCESSO");

        } catch (validectionException $e) {
            
            return redirect()->back()->withErros($e->validator)->withInput();
        } catch(QueryException $e){
            return redirect()->back()->with("ERROR","ERRO AO SALVAR PRODUTO. TENTE NOVAMENTE");
        }
        
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $valor=produto::find($id);
        if (!$valor) {
            # code...
            return redirect()->back()->with("ERRO","FUNCIONÁRIO NÃO ENCONTRADO");
        } 
        return view('pages.admin.produto',compact('valor'));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $valor=produto::find($id);
        if(!$valor){
            return redirect()->back()->with("ERRO","PRODUTO NÃO ENCONTRADO");
        }
        $valor->delete();
        return redirect()->back()->with("SUCESSO","PRODUTO APAGADO COM SUCESSO");
    }
}
