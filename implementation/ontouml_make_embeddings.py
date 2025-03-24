import json
import torch
import pandas as pd
from imagebind import data
from imagebind.models import imagebind_model
from imagebind.models.imagebind_model import ModalityType

# Load the JSON file
with open("ontouml/name_values_extracted.json", "r") as f:
    name_values = json.load(f)

# Select at most 2 keys
selected_keys = list(name_values.keys())[:15]

# Prepare text list and mapping
text_list = []
key_value_map = {}

for key in selected_keys:
    values = name_values[key]
    key_value_map[key] = values
    text_list.extend(values)

# Check for CUDA availability
device = "cuda:0" if torch.cuda.is_available() else "cpu"

# Instantiate model
model = imagebind_model.imagebind_huge(pretrained=True)
model.eval()
model.to(device)

# Load data
inputs = {
    ModalityType.TEXT: data.load_and_transform_text(text_list, device),
}

# Compute embeddings
with torch.no_grad():
    embeddings = model(inputs)

# Compute softmax similarity matrix
softmax_scores = torch.softmax(embeddings[ModalityType.TEXT] @ embeddings[ModalityType.TEXT].T, dim=-1)

# Convert softmax scores to a NumPy array and save as CSV
softmax_array = softmax_scores.cpu().numpy()
df = pd.DataFrame(softmax_array, index=text_list, columns=text_list)
df.to_csv("ontouml/text_softmax_scores.csv")

# Convert embeddings to a list for JSON storage
embeddings = embeddings[ModalityType.TEXT].cpu().numpy()

# Map embeddings back to key-value structure
output_data = {}
index = 0
for key in selected_keys:
    output_data[key] = [
        {"value": value, "embedding": embeddings[index].tolist()} for index, value in enumerate(key_value_map[key])
    ]

# Save to JSON file
with open("ontouml/name_embeddings.json", "w") as f:
    json.dump(output_data, f, indent=4)
