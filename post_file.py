# pip install requests

import requests

# ZMIENIAJ TYLKO TO:
path = "tajny_plik.html"
########

upload_url = f'http://127.0.0.1:7000/upload?file={path}'

nazwa_pliku = "fake_conf.json"
files = {'file': (nazwa_pliku, open(nazwa_pliku, 'rb'))}


username = 'user1'
password = 'user1_password'


response = requests.post(upload_url, files=files, auth=(username, password))

if response.status_code == 200:
    print(response.text)
else:
    print(f"Status Code: {response.status_code}")
    print(response.text)
