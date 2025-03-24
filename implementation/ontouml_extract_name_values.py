import json

# Define file paths
input_file = "ontouml/allontologies.json"
output_file = "ontouml/name_values_extracted.json"

def extract_names(data):
    """ Recursively extract all 'name' values from nested JSON, removing nulls and duplicates. """
    names = set()
    if isinstance(data, dict):
        if 'name' in data and data['name'] is not None:
            names.add(data['name'])  # Store the name value
        for value in data.values():
            names.update(extract_names(value))  # Recurse for nested structures
    elif isinstance(data, list):
        for item in data:
            names.update(extract_names(item))  # Recurse for lists
    return names

# Load input JSON
with open(input_file, "r", encoding="utf-8") as f:
    all_ontologies = json.load(f)

# Extract all 'name' values for each ontology folder
name_values = {key: sorted(extract_names(value)) for key, value in all_ontologies.items()}

# Save extracted name values to JSON
with open(output_file, "w", encoding="utf-8") as f:
    json.dump(name_values, f, indent=4, ensure_ascii=False)

print(f"Extracted name values saved to {output_file}")
