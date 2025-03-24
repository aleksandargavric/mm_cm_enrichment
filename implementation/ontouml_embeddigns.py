import json
import torch
import pandas as pd
from imagebind import data
from imagebind.models import imagebind_model
from imagebind.models.imagebind_model import ModalityType

# ==== PARAMETERS ====
BATCH_SIZE = 10   # Set this to the number of keys per batch
START_BATCH_INDEX = 3  # Start processing from this batch index (1-based)
INPUT_JSON = "ontouml/name_values_extracted.json"
CSV_OUTPUT_TEMPLATE = "ontouml/text_softmax_scores_{}.csv"
JSON_OUTPUT_TEMPLATE = "ontouml/name_embeddings_{}.json"

# ==== LOAD DATA ====
with open(INPUT_JSON, "r") as f:
    name_values = json.load(f)

all_keys = list(name_values.keys())
total_batches = (len(all_keys) + BATCH_SIZE - 1) // BATCH_SIZE

# ==== DEVICE SETUP ====
device = "cuda:0" if torch.cuda.is_available() else "cpu"

# ==== LOAD MODEL ====
model = imagebind_model.imagebind_huge(pretrained=True)
model.eval()
model.to(device)

# ==== ITERATE OVER BATCHES ====
for batch_index in range(START_BATCH_INDEX, total_batches):
    start_idx = batch_index * BATCH_SIZE
    end_idx = min(start_idx + BATCH_SIZE, len(all_keys))
    selected_keys = all_keys[start_idx:end_idx]

    # ==== PREPARE TEXT AND MAPPING ====
    text_list = []
    key_value_map = {}

    for key in selected_keys:
        values = name_values[key]
        key_value_map[key] = values
        text_list.extend(values)

    if not text_list:
        continue

    # ==== PROCESS TEXT ====
    inputs = {
        ModalityType.TEXT: data.load_and_transform_text(text_list, device),
    }

    with torch.no_grad():
        embeddings = model(inputs)

    # ==== SIMILARITY MATRIX ====
    softmax_scores = torch.softmax(embeddings[ModalityType.TEXT] @ embeddings[ModalityType.TEXT].T, dim=-1)
    softmax_array = softmax_scores.cpu().numpy()
    df = pd.DataFrame(softmax_array, index=text_list, columns=text_list)
    df.to_csv(CSV_OUTPUT_TEMPLATE.format(batch_index))

    # ==== EMBEDDING OUTPUT ====
    embeddings_np = embeddings[ModalityType.TEXT].cpu().numpy()
    output_data = {}
    idx = 0
    for key in selected_keys:
        output_data[key] = []
        for value in key_value_map[key]:
            output_data[key].append({
                "value": value,
                "embedding": embeddings_np[idx].tolist()
            })
            idx += 1

    with open(JSON_OUTPUT_TEMPLATE.format(batch_index), "w") as f:
        json.dump(output_data, f, indent=4)

    print(f"Processed batch {batch_index+1}/{total_batches}")
