<?php

namespace App\Http\Controllers;

use App\Models\categoria;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
      public function index()
    {
        //
        $valor = categoria::orderBy('nome', 'asc')->get();
        return view('pages.admin.categoria', compact('valor'));
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
            ];

            $request->validate($rules, [
                'nome.required' => 'O NOME É OBRIGATÓRIO!',
                'nome.regex' => 'O NOME DEVE CONTER APENAS LETRAS!',
            ]);

            // Verificar se já existe categoria igual
            $categoriaDuplicado = categoria::where('nome', $request->nome);

            if ($request->filled('id')) {
                $categoriaDuplicado->where('id', '!=', $request->id);
            }

            if ($categoriaDuplicado->exists()) {
                return redirect()->back()->withInput()->with("ERRO", "JÁ EXISTE UMA CATEGORIA COM O MESMO NOME.");
            }

            $valor = $request->filled('id') ? categoria::find($request->id) : new categoria();

            //PREENCHER OS CAMPOS DO FORMULARIO
            $valor->nome=$request->nome;
            $valor->funcionario_id = Auth::guard('funcionario')->user()->id;
            $valor->save();
            return redirect()->back()->with("SUCESSO",$request->filled('id') ? "CATEGORIA ACTUALIZADO COM SUCESSO" : "categoria CADASTRADO COM SUCESSO");

        } catch (validectionException $e) {
            
            return redirect()->back()->withErros($e->validator)->withInput();
        } catch(QueryException $e){
            return redirect()->back()->with("ERROR","ERRO AO SALVAR CATEGORIA. TENTE NOVAMENTE");
        }
        
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $valor=categoria::find($id);
        if (!$valor) {
            # code...
            return redirect()->back()->with("ERRO","CATEGORIA NÃO ENCONTRADO");
        } 
        return view('pages.admin.categoria',compact('valor'));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $valor=categoria::find($id);
        if(!$valor){
            return redirect()->back()->with("ERRO","CATEGORIA NÃO ENCONTRADO");
        }
        $valor->delete();
        return redirect()->back()->with("SUCESSO","CATEGORIA APAGADO COM SUCESSO");
    }
}
