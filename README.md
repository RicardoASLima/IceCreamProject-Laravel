Comandos que mais vai usar

  1: composer install // Baixar as dependencias 
  2: php artisan serve // Rodar o Servidor
  
Para rodar e criar o migrate
  obs: Esses comando do migrate afeta diretamente o banco
  
  1: php artisan make:migration create_nomeDaTabela_table // Criar uma tabela
  2: php artisan make:migration add_nomeDaColuna_to_nomeDaTabela_table --table=nomeDaTabela // Criar uma coluna em uma tabela existente
  
  Se fez alguma coisa errada e quer dar rollback
  
    1: php artisan migrate:rollback
  
