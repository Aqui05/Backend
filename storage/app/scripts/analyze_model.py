import pickle
import pandas as pd
import sys
import json

def analyze_model(model_path, data):
    # Charger le modèle
    with open(model_path, 'rb') as file:
        model = pickle.load(file)

    # Convertir les données en DataFrame
    df = pd.DataFrame([data])

    # Afficher les noms de colonnes pour le débogage
    print("Colonnes dans les données d'entrée:", df.columns.tolist())

    # Afficher les noms de caractéristiques attendus par le modèle
    if hasattr(model, 'feature_names_in_'):
        print("Caractéristiques attendues par le modèle:", model.feature_names_in_.tolist())

    # Vérifier si toutes les colonnes nécessaires sont présentes
    missing_columns = set(model.feature_names_in_) - set(df.columns)
    if missing_columns:
        return {"error": f"Colonnes manquantes: {', '.join(missing_columns)}"}

    # Réordonner les colonnes pour correspondre à l'ordre attendu par le modèle
    df = df[model.feature_names_in_]

    # Faire des prédictions
    try:
        predictions = model.predict(df)
        return predictions.tolist()  # Convertir en liste pour la sérialisation JSON
    except Exception as e:
        return {"error": str(e)}

if __name__ == "__main__":
    model_path = sys.argv[1]
    data_json = sys.argv[2]
    data = json.loads(data_json)
    predictions = analyze_model(model_path, data)
    print(json.dumps(predictions))
