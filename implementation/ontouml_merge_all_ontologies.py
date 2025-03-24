import os
import json

# Define paths
base_dir = "ontouml/ontouml-models/models"
output_file = "ontouml/allontologies.json"

# Dictionary to hold merged data
all_ontologies = {}

# Iterate through directories in base_dir
for folder in os.listdir(base_dir):
    folder_path = os.path.join(base_dir, folder)
    ontology_path = os.path.join(folder_path, "ontology.json")
    
    # Check if it's a directory and contains ontology.json
    if os.path.isdir(folder_path) and os.path.isfile(ontology_path):
        try:
            with open(ontology_path, "r", encoding="utf-8") as f:
                all_ontologies[folder] = json.load(f)  # Store JSON under the folder name
        except Exception as e:
            print(f"Error reading {ontology_path}: {e}")

# Write merged JSON to file
with open(output_file, "w", encoding="utf-8") as f:
    json.dump(all_ontologies, f, indent=4, ensure_ascii=False)

print(f"Merged ontologies saved to {output_file}")
