import json

# Define file paths
input_file = "ontouml/name_values_extracted.json"
output_file = "ontouml/name_sentences.json"

# Read the input JSON file
with open(input_file, "r", encoding="utf-8") as f:
    data = json.load(f)

# Process the data
processed_data = {
    key: ", ".join(values) for key, values in data.items()
}

# Save the processed data to the output file
with open(output_file, "w", encoding="utf-8") as f:
    json.dump(processed_data, f, indent=4, ensure_ascii=False)

print(f"Processed data saved to {output_file}")
