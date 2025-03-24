import json

# Define file paths
input_file = "ontouml/allontologies.json"
output_file = "ontouml/filter_ontologies.json"

def filter_structure(data):
    """ Recursively filter out everything except 'name' and necessary array structures."""
    if isinstance(data, dict):
        filtered = {}
        if 'name' in data:
            filtered['name'] = data['name']  # Keep only name
        for key, value in data.items():
            if isinstance(value, (list, dict)):
                filtered[key] = filter_structure(value)  # Recurse for nested structures
        return filtered
    elif isinstance(data, list):
        return [filter_structure(item) for item in data]  # Recurse for lists
    return data  # Ignore non-dict/list types

# Load input JSON
with open(input_file, "r", encoding="utf-8") as f:
    all_ontologies = json.load(f)

# Filter data
filtered_ontologies = {key: filter_structure(value) for key, value in all_ontologies.items()}

# Save filtered JSON
with open(output_file, "w", encoding="utf-8") as f:
    json.dump(filtered_ontologies, f, indent=4, ensure_ascii=False)

print(f"Filtered ontologies saved to {output_file}")
