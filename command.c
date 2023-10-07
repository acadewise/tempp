/*
 * Lab 3: Shell project
 *
 * Template file.
 * You will need to add more code here to execute the command table.
 *
 * NOTE: You are responsible for fixing any bugs this code may have!
 *
 * 
 */

#include "command.h"

#include <stdio.h>
#include <stdlib.h>

#include "shell.h"

/*
 *  Initialize a command_t
 */

void create_command(command_t *command) {
  command->single_commands = NULL;

  command->out_file = NULL;
  command->in_file = NULL;
  command->err_file = NULL;

  command->append_out = false;
  command->append_err = false;
  command->background = false;

  command->num_single_commands = 0;
} /* create_command() */

/*
 *  Insert a single command into the list of single commands in a command_t
 */

void insert_single_command(command_t *command, single_command_t *simp) {
  if (simp == NULL) {
    return;
  }

  command->num_single_commands++;
  int new_size = command->num_single_commands * sizeof(single_command_t *);
  command->single_commands = (single_command_t **)
                              realloc(command->single_commands,
                                      new_size);
  command->single_commands[command->num_single_commands - 1] = simp;
} /* insert_single_command() */

/*
 *  Free a command and its contents
 */

void free_command(command_t *command) {
  for (int i = 0; i < command->num_single_commands; i++) {
    free_single_command(command->single_commands[i]);
  }

  if (command->out_file) {
    free(command->out_file);
    command->out_file = NULL;
  }

  if (command->in_file) {
    free(command->in_file);
    command->in_file = NULL;
  }

  if (command->err_file) {
    free(command->err_file);
    command->err_file = NULL;
  }

  command->append_out = false;
  command->append_err = false;
  command->background = false;

  free(command);
} /* free_command() */

/*
 *  Print the contents of the command in a pretty way
 */

void print_command(command_t *command) {
  printf("\n\n");
  printf("              COMMAND TABLE                \n");
  printf("\n");
  printf("  #   single Commands\n");
  printf("  --- ----------------------------------------------------------\n");

  // iterate over the single commands and print them nicely
  for (int i = 0; i < command->num_single_commands; i++) {
    printf("  %-3d ", i );
    print_single_command(command->single_commands[i]);
  }

  printf( "\n\n" );
  printf( "  Output       Input        Error        Background\n" );
  printf( "  ------------ ------------ ------------ ------------\n" );
  printf( "  %-12s %-12s %-12s %-12s\n",
            command->out_file?command->out_file:"default",
            command->in_file?command->in_file:"default",
            command->err_file?command->err_file:"default",
            command->background?"YES":"NO");
  printf( "\n\n" );
} /* print_command() */

/*
 *  Execute a command
 */

void execute_command(command_t *command) {
  // Don't do anything if there are no single commands
  if (command->single_commands == NULL) {
    print_prompt();
    return;
  }

  // Iterate over the single commands and execute them
  for (int i = 0; i < command->num_single_commands; i++) {
    int pipe_fd[2]; // Pipe file descriptors for connecting processes
    pid_t child_pid;
    
    // Create a pipe if there's a command after this one
    if (i < command->num_single_commands - 1) {
      if (pipe(pipe_fd) == -1) {
        perror("pipe");
        exit(EXIT_FAILURE);
      }
    }

    // Fork a new process
    child_pid = fork();
    if (child_pid == -1) {
      perror("fork");
      exit(EXIT_FAILURE);
    }
    
    if (child_pid == 0) { // Child process
      // If there's a command after this one, redirect stdout to the write end of the pipe
      if (i < command->num_single_commands - 1) {
        close(pipe_fd[0]); // Close the read end of the pipe
        dup2(pipe_fd[1], STDOUT_FILENO); // Redirect stdout to the write end of the pipe
        close(pipe_fd[1]); // Close the original write end of the pipe
      }

      // Set up input and output redirection for this single command
      if (command->in_file) {
        int in_fd = open(command->in_file, O_RDONLY);
        if (in_fd == -1) {
          perror("open");
          exit(EXIT_FAILURE);
        }
        dup2(in_fd, STDIN_FILENO);
        close(in_fd);
      }

      if (command->out_file) {
        int out_flags = O_WRONLY | O_CREAT;
        if (command->append_out) {
          out_flags |= O_APPEND;
        } else {
          out_flags |= O_TRUNC;
        }
        int out_fd = open(command->out_file, out_flags, 0666);
        if (out_fd == -1) {
          perror("open");
          exit(EXIT_FAILURE);
        }
        dup2(out_fd, STDOUT_FILENO);
        close(out_fd);
      }

      // Execute the single command
      execvp(command->single_commands[i]->argv[0], command->single_commands[i]->argv);
      perror("execvp"); // If execvp fails, print an error
      exit(EXIT_FAILURE);
    } else { // Parent process
      // Close the write end of the pipe if there's a command after this one
      if (i < command->num_single_commands - 1) {
        close(pipe_fd[1]);
      }
      
      // If this command is not in the background, wait for it to finish
      if (!command->background) {
        int status;
        waitpid(child_pid, &status, 0);
      }
    }
  }

  // Print new prompt
  print_prompt();
}