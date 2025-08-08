  <!DOCTYPE html>
  <html lang="pt-br">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastro de Usuários - Vue.js</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

    <style>
      :root{
        --radius: 14px;
        --radius-pill: 999px;
        --shadow-1: 0 6px 18px rgba(0,0,0,.06);
        --shadow-2: 0 10px 28px rgba(0,0,0,.08);
      }
      body { padding-top: 3rem !important; background: #f8f9fa; }
      #mensagem { font-weight: 600; text-align: center; margin-top: 15px; }
      .cadastro-wrapper{ max-width: 560px; margin: 0 auto; }
      #form-cadastro{ border-radius: var(--radius); box-shadow: var(--shadow-1); background: #fff; }
      #form-cadastro:hover{ box-shadow: var(--shadow-2); transform: translateY(-1px); }
      .botoes-direita{ display: flex; justify-content: end; gap: .5rem; }
      .is-valid{ border-color: var(--bs-success) !important; }
      .is-invalid{ border-color: var(--bs-danger) !important; }
      #tabela-usuarios tbody tr:hover{ background-color: rgba(0,0,0,.02); transform: translateY(-1px); }
    </style>
  </head>
  <body>

  <div id="app" class="container">

    <h1 class="text-center mb-4">{{ editando ? "Editar Usuário" : "Cadastro" }}</h1>

    <div class="cadastro-wrapper">

      <div v-if="editando" class="alert alert-warning text-center">
        Você está editando um usuário.
      </div>

      <form id="form-cadastro" class="p-4" @submit.prevent="salvarUsuario">

        <div class="mb-3 row">
          <label for="nome" class="col-sm-2 col-form-label text-end">Nome:</label>
          <div class="col-sm-10">
            <input type="text" id="nome" v-model="form.name" class="form-control"
                  :class="validacoes.nome" required />
          </div>
        </div>

        <div class="mb-3 row">
          <label for="usuario" class="col-sm-2 col-form-label text-end">Usuário:</label>
          <div class="col-sm-10">
            <input type="text" id="usuario" v-model="form.username" class="form-control"
                  :class="validacoes.usuario" required />
          </div>
        </div>

        <div class="mb-3 row">
          <label for="password" class="col-sm-2 col-form-label text-end">Senha:</label>
          <div class="col-sm-10">
            <div class="input-group">
              <input :type="mostrarSenha ? 'text' : 'password'" id="password"
                    v-model="form.password" class="form-control"
                    :class="validacoes.password" required minlength="6" />
              <button type="button" class="btn btn-outline-secondary" @click="mostrarSenha = !mostrarSenha" :title="mostrarSenha ? 'Ocultar senha' : 'Mostrar senha'">
                <i class="bi" :class="mostrarSenha ? 'bi-eye-slash' : 'bi-eye'"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="botoes-direita">
          <button type="submit" class="btn btn-success" :disabled="carregando">
            <span v-if="carregando" class="spinner-border spinner-border-sm me-2"></span>
            {{ editando ? "Atualizar" : "Cadastrar" }}
          </button>
          <button v-if="editando" type="button" class="btn btn-danger" @click="cancelarEdicao" :disabled="carregando">
            Cancelar
          </button>
        </div>
      </form>
    </div>

    <div v-if="mensagem.texto" :class="`alert mt-3 ${mensagem.sucesso ? 'alert-success' : 'alert-danger'}`" role="alert">
      <i class="bi" :class="mensagem.sucesso ? 'bi-check-circle' : 'bi-exclamation-triangle'"></i>
      {{ mensagem.texto }}
    </div>

    <div class="input-group my-4" style="max-width: 500px; margin: auto;">
      <input type="text" class="form-control" placeholder="Buscar por nome ou usuário"
            v-model="termoBusca" :disabled="carregando" @keyup.enter="buscarUsuarios" aria-label="Buscar usuários" />
      <button class="btn btn-outline-primary" @click="buscarUsuarios" :disabled="carregando" aria-label="Buscar">
        <i class="bi bi-search"></i> Buscar
      </button>
    </div>

    <table v-if="usuariosExibidos.length" class="table table-bordered table-striped table-hover mt-4">
      <thead class="table-secondary">
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Usuário</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="u in usuariosExibidos" :key="u.id">
          <td>{{ u.id }}</td>
          <td>{{ u.name }}</td>
          <td>{{ u.username }}</td>
          <td>
            <button class="btn btn-sm btn-warning me-1" @click="editarUsuario(u)" :disabled="carregando" title="Editar usuário">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-sm btn-danger" @click="deletarUsuario(u.id)" :disabled="carregando" title="Excluir usuário">
              <i class="bi bi-trash"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else class="text-center text-muted mt-4" style="font-style: italic;">
      Nenhum usuário encontrado.
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>

  <script>
  const { createApp } = Vue;

  createApp({
    data() {
      return {
        usuarios: [],
        usuariosExibidos: [],
        termoBusca: '',
        form: { id: '', name: '', username: '', password: '' },
        editando: false,
        mostrarSenha: false,
        carregando: false,
        mensagem: { texto: '', sucesso: true }
      };
    },
    computed: {
      validacoes() {
        return {
          nome: this.form.name.length > 2 ? 'is-valid' : (this.form.name.length > 0 ? 'is-invalid' : ''),
          usuario: this.form.username.length > 2 ? 'is-valid' : (this.form.username.length > 0 ? 'is-invalid' : ''),
          password: this.editando
            ? '' 
            : (this.form.password.length >= 6 ? 'is-valid' : (this.form.password.length > 0 ? 'is-invalid' : ''))
        };
      }
    },
    methods: {
      listarUsuarios() {
        fetch('read.php', {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(r => r.json())
          .then(data => {
            this.usuarios = Array.isArray(data) ? data : [];
            this.usuariosExibidos = this.usuarios;
          })
          .catch(() => this.exibirMensagem('Erro ao carregar usuários.', false));
      },
      buscarUsuarios() {
        const termo = this.termoBusca.trim().toLowerCase();
        if (!termo) {
          this.usuariosExibidos = this.usuarios;
        } else {
          this.usuariosExibidos = this.usuarios.filter(u =>
            u.name.toLowerCase().includes(termo) ||
            u.username.toLowerCase().includes(termo)
          );
        }
      },
      salvarUsuario() {
        if (!this.validarFormulario()) {
          this.exibirMensagem('Preencha os campos corretamente.', false);
          return;
        }
        this.carregando = true;
        const url = this.editando ? 'update.php' : 'create.php';

        const dados = new URLSearchParams();
        if (this.editando) dados.append('id', this.form.id);
        dados.append('name', this.form.name);
        dados.append('username', this.form.username);
        dados.append('password', this.form.password);

        fetch(url, {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
          },
          body: dados.toString()
        })
          .then(r => r.text())
          .then(msg => {
            this.exibirMensagem(msg, msg.includes('sucesso'));
            this.listarUsuarios();
            this.resetarFormulario();
          })
          .catch(() => this.exibirMensagem('Erro ao salvar.', false))
          .finally(() => this.carregando = false);
      },
      editarUsuario(u) {
        this.form = { id: u.id, name: u.name, username: u.username, password: '' };
        this.editando = true;
        this.mostrarSenha = false;
        this.termoBusca = '';
        this.usuariosExibidos = this.usuarios;
      },
      cancelarEdicao() {
        this.resetarFormulario();
      },
      deletarUsuario(id) {
        if (!confirm('Deseja excluir este usuário?')) return;
        this.carregando = true;
        const dados = new URLSearchParams();
        dados.append('id', id);
        fetch('delete.php', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: dados
        })
          .then(r => r.text())
          .then(msg => {
            this.exibirMensagem(msg, msg.includes('sucesso'));
            this.listarUsuarios();
          })
          .catch(() => this.exibirMensagem('Erro ao deletar.', false))
          .finally(() => this.carregando = false);
      },
      resetarFormulario() {
        this.form = { id: '', name: '', username: '', password: '' };
        this.editando = false;
        this.mostrarSenha = false;
        this.termoBusca = '';
        this.usuariosExibidos = this.usuarios;
      },
      exibirMensagem(texto, sucesso) {
        this.mensagem = { texto, sucesso };
        setTimeout(() => this.mensagem.texto = '', 4000);
      },
      validarFormulario() {
        const nomeValido = this.form.name.length > 2;
        const usuarioValido = this.form.username.length > 2;
        const senhaValida = this.editando ? true : this.form.password.length >= 6;
        return nomeValido && usuarioValido && senhaValida;
      }
    },
    mounted() {
      this.listarUsuarios();
    }
  }).mount('#app');
  </script>

  </body>
  </html>
