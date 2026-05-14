import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin

BASE_URLS = [
    "https://www.vavilovsar.ru//upravlenie-obespecheniya-kachestva-obrazovaniya/struktura/otdel-organizacii-uchebnogo-processa/uk1/institut-genetiki-i-agronomii",
    "https://www.vavilovsar.ru//upravlenie-obespecheniya-kachestva-obrazovaniya/struktura/otdel-organizacii-uchebnogo-processa/uk1/institut-agrobiznesa",
    "https://www.vavilovsar.ru//upravlenie-obespecheniya-kachestva-obrazovaniya/struktura/otdel-organizacii-uchebnogo-processa/uk1/ekzamenacionnaya-sessiya",
    "https://www.vavilovsar.ru//upravlenie-obespecheniya-kachestva-obrazovaniya/struktura/otdel-organizacii-uchebnogo-processa/uk1/nedeli-semestra-uchebnogo-goda",
    "https://www.vavilovsar.ru//upravlenie-obespecheniya-kachestva-obrazovaniya/struktura/otdel-organizacii-uchebnogo-processa/uk2/institut-injenerii-i-robototexniki",
    "https://www.vavilovsar.ru//upravlenie-obespecheniya-kachestva-obrazovaniya/struktura/otdel-organizacii-uchebnogo-processa/uk2/ekzamenacionnaya-sessiya",
    "https://www.vavilovsar.ru//upravlenie-obespecheniya-kachestva-obrazovaniya/struktura/otdel-organizacii-uchebnogo-processa/uk3/institut-biotexnologii",
    "https://www.vavilovsar.ru//upravlenie-obespecheniya-kachestva-obrazovaniya/struktura/otdel-organizacii-uchebnogo-processa/uk3/institut-veterinarnoi-mediciny-i-farmacii",
    "https://www.vavilovsar.ru//upravlenie-obespecheniya-kachestva-obrazovaniya/struktura/otdel-organizacii-uchebnogo-processa/uk3/ekzamenacionnaya-sessiya",
    "https://www.vavilovsar.ru//upravlenie-nauchno-innovacionnoi-deyatelnosti/aspirantura/raspisanie",
]

POSTFIXES = [
    "/ochno-zaochnaya-forma-obucheniya",
    "/zaochnaya-forma-obucheniya",
    "/ochnaya-forma-obucheniya",
]

HEADERS = {
    "User-Agent": "Mozilla/5.0"
}

pdf_links = set()

for base_url in BASE_URLS:
    for postfix in POSTFIXES:
        url = base_url.rstrip("/") + postfix

        try:
            response = requests.get(
                url,
                headers=HEADERS,
                timeout=15
            )

            if response.status_code != 200:
                print(f"[SKIP] {url} -> {response.status_code}")
                continue

            soup = BeautifulSoup(response.text, "html.parser")

            for tag in soup.find_all("a", href=True):
                href = tag["href"].strip()

                if href.lower().endswith(".pdf"):
                    full_url = urljoin(url, href)
                    pdf_links.add(full_url)

            print(f"[OK] {url}")

        except Exception as e:
            print(f"[ERROR] {url} -> {e}")

with open("pdf_links.txt", "w", encoding="utf-8") as file:
    for link in sorted(pdf_links):
        file.write(link + "\n")

print(f"\nНайдено PDF файлов: {len(pdf_links)}")
print("Ссылки сохранены в pdf_links.txt")