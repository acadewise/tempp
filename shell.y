/*
 * Lab 3
 * shell.y: parser for shell
 *
 * This parser compiles the following grammar:
 *
 *	cmd [arg]* [> filename]
 *
 * you must extend it to understand the complete shell grammar
 *
 */

%code requires 
{
#include <sys/types.h>
#include <stdio.h>
#include <string.h>
#include <regex.h>
#include <dirent.h>
#include <malloc.h>
#include <stdbool.h>
#include <stdlib.h>
#include <unistd.h>
#include <fcntl.h>
}

%union
{
  char *string_val;
}

%token <string_val> WORD
%token NOTOKEN GREAT NEWLINE LESS PIPE AMPERSAND GREATGREAT GREATAND TWOGREAT GREATGREATAND

%{
#include <stdio.h>
#include "shell.h"

void yyerror(const char * s);
void expandWildcardsIfNecessary(char *arg);
void expandWildcard(char *prefix, char *suffix);
bool cmpfunction (char *i, char *j);

int yylex();
static char **_sortArgument = NULL;
static int _sortArgumentSize = 0;
static int _sortArgumentCount = 0;
static bool wildCard;
%}

%%

goal:
  commands
  ;

commands:
  command
  | commands command
  ;

command: 
  pipe_list iomodifier_list background_opt NEWLINE {
    Shell::_currentCommand.execute();
  }
  | NEWLINE {
    Shell::_currentCommand.execute();
  }
  | error NEWLINE { yyerrok; }
  ;

simple_command:	
  command_and_args 
  ;

command_and_args:
  command_word argument_list {    
    Shell::_currentCommand.insertSimpleCommand(Command::_currentSimpleCommand);
  }
  ;

argument_list:
  argument_list argument
  | /* can be empty */
  ;

argument:
  WORD {
    wildCard = false;
    char *p = (char *)"";
    expandWildcard(p, $1);
    qsort(_sortArgument, _sortArgumentCount, sizeof(char *), cmpfunction);
    for (int i = 0; i < _sortArgumentCount; i++) {
      Command::_currentSimpleCommand->insertArgument(_sortArgument[i]);
      free(_sortArgument[i]); // Free allocated memory
    }
    free(_sortArgument); // Free the array itself
    _sortArgument = NULL;
    _sortArgumentSize = 0;
    _sortArgumentCount = 0;
  }
  ;

command_word:
  WORD {
    Command::_currentSimpleCommand = new SimpleCommand();
    Command::_currentSimpleCommand->insertArgument($1);
  }
  ;

pipe_list:
  pipe_list PIPE simple_command 
  | simple_command 
  ;

iomodifier_opt:
  GREAT WORD {
    Shell::_currentCommand.redirect(1, $2);
  }
  | GREATGREAT WORD {
    Shell::_currentCommand.redirect(1, $2);
    Shell::_currentCommand._append = true;
  }
  | GREATAND WORD {
    Shell::_currentCommand.redirect(1, $2);
    Shell::_currentCommand.redirect(2, $2);
  }
  | GREATGREATAND WORD {
    Shell::_currentCommand.redirect(1, $2);
    Shell::_currentCommand.redirect(2, $2);
    Shell::_currentCommand._append = true;
  }
  | LESS WORD {
    Shell::_currentCommand.redirect(0, $2);
  }
  | TWOGREAT WORD {
    Shell::_currentCommand.redirect(2, $2);
  }
  ;

iomodifier_list:
  iomodifier_list iomodifier_opt
  | /*empty*/
  ;

background_opt:
  AMPERSAND {
    Shell::_currentCommand._background = true;
  }
  | /*empty*/
  ;
%%

bool cmpfunction (const void *i, const void *j) {
  return strcmp(*(const char **)i, *(const char **)j) < 0;
}

void
yyerror(const char * s)
{
  fprintf(stderr,"%s", s);
}

void expandWildcardsIfNecessary(char *arg) {
  char *a = arg;
  char *p;
  char *path;
  
  if (strchr(arg, '?') == NULL && strchr(arg, '*') == NULL) {
    Command::_currentSimpleCommand->insertArgument(arg);
    return;
  }

  DIR *dir;
  
  if (arg[0] == '/') {
    size_t found = 0;
    found = strcspn(arg, "/");
    
    while (strchr(arg + found + 1, '/') != NULL) {
      found = strcspn(arg + found + 1, "/") + found + 1;
    }
    
    path = strndup(arg, found + 1);
    a = arg + found + 1;
    dir = opendir(path);
  } else {
    dir = opendir(".");
    path = (char *)"";
  }
  
  if (dir == NULL) {
    perror("opendir");
    return;
  }

  size_t regSize = 2 * strlen(a) + 10;
  char *reg = (char *)malloc(regSize);
  char *r = reg;
  *r = '^';
  r++;

  while (*a) {
    if (*a == '*') {
      *r = '.';
      r++;
      *r = '*';
      r++;
    } else if (*a == '?') {
      *r = '.';
      r++;
    } else if (*a == '.') {
      *r = '\\';
      r++;
      *r = '.';
      r++;
    } else {
      *r = *a;
      r++;
    }
    a++;
  }

  *r = '$';
  r++;
  *r = 0;

  regex_t re;
  int expbuf = regcomp(&re, reg, REG_EXTENDED | REG_NOSUB);

  if (expbuf != 0) {
    perror("regcomp");
    return;
  }

  struct dirent *ent;

  while ((ent = readdir(dir)) != NULL) {
    if (regexec(&re, ent->d_name, 1, NULL, 0) == 0) {
      if (reg[1] == '.') {
        if (ent->d_name[0] != '.') {
          char *name = (char *)malloc(strlen(path) + strlen(ent->d_name) + 1);
          strcpy(name, path);
          strcat(name, ent->d_name);
          _sortArgumentCount++;
          _sortArgumentSize = _sortArgumentSize + sizeof(char *);
          _sortArgument = (char **)realloc(_sortArgument, _sortArgumentSize);
          _sortArgument[_sortArgumentCount - 1] = name;
        }
      } else {
        char *name = (char *)malloc(strlen(path) + strlen(ent->d_name) + 1);
        strcpy(name, path);
        strcat(name, ent->d_name);
        _sortArgumentCount++;
        _sortArgumentSize = _sortArgumentSize + sizeof(char *);
        _sortArgument = (char **)realloc(_sortArgument, _sortArgumentSize);
        _sortArgument[_sortArgumentCount - 1] = name;
      }
    }
  }

  closedir(dir);
  regfree(&re);

  qsort(_sortArgument, _sortArgumentCount, sizeof(char *), cmpfunction);
  
  for (int i = 0; i < _sortArgumentCount; i++) {
    Command::_currentSimpleCommand->insertArgument(_sortArgument[i]);
    free(_sortArgument[i]); // Free allocated memory
  }
  
  free(_sortArgument); // Free the array itself
  _sortArgument = NULL;
  _sortArgumentSize = 0;
  _sortArgumentCount = 0;
}

void expandWildcard(char *prefix, char *suffix) {
  if (suffix[0] == 0) {
    _sortArgumentCount++;
    _sortArgumentSize = _sortArgumentSize + sizeof(char *);
    _sortArgument = (char **)realloc(_sortArgument, _sortArgumentSize);
    _sortArgument[_sortArgumentCount - 1] = strdup(prefix);
    return;
  }

  char Prefix[MAXFILENAME];

  if (prefix[0] == 0) {
    if (suffix[0] == '/') {
      suffix += 1;
      sprintf(Prefix, "%s/", prefix);
    } else {
      strcpy(Prefix, prefix);
    }
  } else {
    sprintf(Prefix, "%s/", prefix);
  }

  char *s = strchr(suffix, '/');
  char component[MAXFILENAME];

  if (s != NULL) {
    strncpy(component, suffix, s - suffix);
    component[s - suffix] = 0;
    suffix = s + 1;
  } else {
    strcpy(component, suffix);
    suffix = suffix + strlen(suffix);
  }

  char newPrefix[MAXFILENAME];

  if (strchr(component, '?') == NULL && strchr(component, '*') == NULL) {
    if (Prefix[0] == 0) {
      strcpy(newPrefix, component);
    } else {
      sprintf(newPrefix, "%s%s", prefix, component);
    }
    
    expandWildcard(newPrefix, suffix);
    return;
  }

  size_t regSize = 2 * strlen(component) + 10;
  char *reg = (char *)malloc(regSize);
  char *r = reg;
  *r = '^';
  r++;

  int i = 0;

  while (component[i]) {
    if (component[i] == '*') {
      *r = '.';
      r++;
      *r = '*';
      r++;
    } else if (component[i] == '?') {
      *r = '.';
      r++;
    } else if (component[i] == '.') {
      *r = '\\';
      r++;
      *r = '.';
      r++;
    } else {
      *r = component[i];
      r++;
    }
    i++;
  }

  *r = '$';
  r++;
  *r = 0;

  regex_t re;
  int expbuf = regcomp(&re, reg, REG_EXTENDED | REG_NOSUB);

  char *dir;

  if (Prefix[0] == 0) {
    dir = (char *)".";
  } else {
    dir = Prefix;
  }
  
  DIR *d = opendir(dir);

  if (d == NULL) {
    return;
  }

  struct dirent *ent;
  bool find = false;

  while ((ent = readdir(d)) != NULL) {
    if (regexec(&re, ent->d_name, 1, NULL, 0) == 0) {
      find = true;
      char *name = (char *)malloc(strlen(Prefix) + strlen(ent->d_name) + 1);
      strcpy(name, Prefix);
      strcat(name, ent->d_name);
      
      if (reg[1] == '.') {
        if (ent->d_name[0] != '.') {
          _sortArgumentCount++;
          _sortArgumentSize = _sortArgumentSize + sizeof(char *);
          _sortArgument = (char **)realloc(_sortArgument, _sortArgumentSize);
          _sortArgument[_sortArgumentCount - 1] = name;
        }
      } else {
        _sortArgumentCount++;
        _sortArgumentSize = _sortArgumentSize + sizeof(char *);
        _sortArgument = (char **)realloc(_sortArgument, _sortArgumentSize);
        _sortArgument[_sortArgumentCount - 1] = name;
      }
    }
  }
  
  if (!find) {
    if (Prefix[0] == 0) {
      strcpy(newPrefix, component);
    } else {
      sprintf(newPrefix, "%s%s", prefix, component);
    }
    
    expandWildcard(newPrefix, suffix);
  }

  closedir(d);
  regfree(&re);
  free(reg);
}

#if 0
main()
{
  yyparse();
}
#endif
