import json
import ollama
import re

# Load the JSON data
with open('ontouml/name_sentences.json', 'r') as file:
    data = json.load(file)

# Load existing responses (if the file exists)
responses_file = 'ontouml/ollama_responses.json'
try:
    with open(responses_file, 'r') as file:
        responses = json.load(file)
except (FileNotFoundError, json.JSONDecodeError):
    responses = {}

# Model name
model_name = 'deepseek-r1:latest'

i = 1
# Iterate over all items in the JSON data
for key, value in data.items():
    # Skip if the key already exists in the responses
    if key in responses:
        print(f'Skipping key: {key} (Already processed)')
        continue

    # Prepare the prompt
    prompt = (
        "You are given a list of names from an OntoUML model. Your task is to analyze the domain of the model and perform the following steps: "
        "1. Domain Identification: Determine the domain of the model based on the given names and describe it in a few sentences. "
        "2. Visual Modality Analysis: Count how many words from the list can be visually observed in an image (i.e., have a recognizable visual representation). Provide a list of such words. "
        "3. Auditory Modality Analysis: Identify how many words and which ones can have an auditory match (i.e., can be perceived through sound). Only count the words that represent objects or actors that can be heard. "
        "4. Adaptation Score Prediction: Estimate an adaptation_score between 0 and 1, representing how much the conceptual model would be enriched by incorporating multimodal data (visual and auditory). "
        "Return the results in a JSON file with the following structure: "
        '{ "name": "{OntoUML Model Name}", "domain": "{Identified Domain}", "description_of_the_domain": "{Brief Description}", '
        '"number_of_visual_matches": {count}, "list_of_visual_matches": ["word1", "word2", ...], '
        '"number_of_auditory_matches": {count}, "list_of_auditory_matches": ["word1", "word2", ...], '
        '"adaptation_score": {value between 0 and 1} }. '
        "The input list is: " + value
    )
    # Call Ollama's chat API
    response = ollama.chat(model=model_name, messages=[{'role': 'user', 'content': prompt}])

    # Extract response content
    response_text = response['message']['content'].strip()

    # Extract the thinking process
    think_match = re.search(r'^(.*?</think>)', response_text, re.DOTALL)
    the_thinking = think_match.group(1) if think_match else ""

    # Extract JSON from the response
    json_match = re.search(r'```json\n(.*)\n```', response_text, re.DOTALL)

    response_data = {
        "the_thinking": the_thinking,
        "value": value,  # Add original value from name_sentences.json
    }

    if json_match:
        extracted_json = json_match.group(1)
        try:
            parsed_json = json.loads(extracted_json)
            response_data.update(parsed_json)
        except json.JSONDecodeError:
            response_data["non-json-response"] = response_text
    else:
        response_data["non-json-response"] = response_text

    # Store response in JSON file
    responses[key] = response_data

    with open(responses_file, 'w') as file:
        json.dump(responses, file, indent=4, ensure_ascii=False)

    print(f'{i} Processed key: {key}')
    i = i + 1

print("All keys processed.")
