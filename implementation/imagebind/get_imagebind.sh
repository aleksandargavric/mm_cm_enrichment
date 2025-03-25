#!/bin/bash

# Define repository URL and target directory name
REPO_URL="https://github.com/facebookresearch/ImageBind.git"
TARGET_DIR="ImageBind"

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
    exit 1
  fi
fi

# Change to the repository directory
cd "$TARGET_DIR" || { echo "Failed to enter directory '$TARGET_DIR'"; exit 1; }

# Install the package using pip
echo "Installing package using pip..."
pip install .
if [ $? -ne 0 ]; then
  echo "pip install failed."
  exit 1
fi

# Run the setup script
echo "Running setup script..."
python setup.py
if [ $? -ne 0 ]; then
  echo "python setup.py failed."
  exit 1
fi

echo "All steps completed successfully."
