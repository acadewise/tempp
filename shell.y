/*
 * Lab 3
 * shell.y: parser for shell
 *
 * This parser compiles the following grammar:
 *
 *   cmd [arg]* [> filename]
 *
 * You must extend it to understand the complete shell grammar
 *
 */

%code requires
{
  #include <stdio.h>
  #include <stdlib.h>
  #include <string.h>
  #include "command.h"
  #include "single_command.h"
  #include "shell.h"

  void yyerror(const char *s);
  int yylex();
}

%union
{
  char *string;
}

%token <string> WORD PIPE
%token NOTOKEN NEWLINE STDOUT

%{

void insert_argument(single_command_t *command, char *arg);
void insert_single_command(command_t *command, single_command_t *single);
command_t *g_current_command;
single_command_t *g_current_single_command;

%}

%%

goal:
  entire_command_list
  ;

entire_command_list:
  entire_command_list entire_command
  | entire_command
  ;

entire_command:
  single_command_list io_modifier_list NEWLINE {
    print_command(g_current_command);
  }
  | NEWLINE
  ;

single_command_list:
  single_command_list PIPE single_command
  | single_command
  ;

single_command:
  executable argument_list {
    insert_single_command(g_current_command, g_current_single_command);
  }
  ;

argument_list:
  argument_list argument
  | /* can be empty */
  ;

argument:
  WORD {
    insert_argument(g_current_single_command, strdup(yylval.string));
  }
  ;

executable:
  WORD {
    insert_argument(g_current_single_command, strdup(yylval.string));
    g_current_single_command->executable = strdup(yylval.string);
  }
  ;

io_modifier_list:
  io_modifier_list io_modifier
  | /* can be empty */
  ;

io_modifier:
  STDOUT WORD {
    // Handle output redirection here if needed
  }
  ;

%%

void yyerror(const char *s)
{
  fprintf(stderr, "%s\n", s);
}

int main()
{
  g_current_command = (command_t *)malloc(sizeof(command_t));
  g_current_single_command = NULL;
  yyparse();
  free(g_current_command); // Free allocated memory
  return 0;
}
