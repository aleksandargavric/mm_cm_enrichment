#!/bin/bash

# Define repository URL and target directory name
REPO_URL="https://github.com/OntoUML/ontouml-models.git"
TARGET_DIR="ontouml-models"

# Check if the directory already exists
if [ -d "$TARGET_DIR" ]; then
  echo "Directory '$TARGET_DIR' already exists. Skipping clone."
else
  echo "Cloning repository from $REPO_URL..."
  git clone "$REPO_URL" "$TARGET_DIR"
  if [ $? -eq 0 ]; then
    echo "Repository cloned successfully into '$TARGET_DIR'."
  else
    echo "An error occurred while cloning the repository."
  fi
fi
