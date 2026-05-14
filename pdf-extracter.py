import os
import json
import time
import random
import requests
import pdfplumber
import pandas as pd

from urllib.parse import urlparse
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry

# =========================
# CONFIG
# =========================

PDF_LINKS_FILE = "pdf_links.txt"

RESULT_DIR = "result"

PDF_DIR = os.path.join(RESULT_DIR, "pdf")
JSON_DIR = os.path.join(RESULT_DIR, "json")
CSV_DIR = os.path.join(RESULT_DIR, "csv")

FAILED_LOG = os.path.join(RESULT_DIR, "failed.txt")

REQUEST_TIMEOUT = 30

MIN_DELAY = 3
MAX_DELAY = 8

MAX_RETRIES = 5

# =========================
# CREATE DIRECTORIES
# =========================

os.makedirs(PDF_DIR, exist_ok=True)
os.makedirs(JSON_DIR, exist_ok=True)
os.makedirs(CSV_DIR, exist_ok=True)

# =========================
# SESSION + RETRY
# =========================

session = requests.Session()

retry_strategy = Retry(
    total=MAX_RETRIES,
    backoff_factor=2,
    status_forcelist=[
        429,
        500,
        502,
        503,
        504
    ],
    allowed_methods=["GET"]
)

adapter = HTTPAdapter(max_retries=retry_strategy)

session.mount("http://", adapter)
session.mount("https://", adapter)

# =========================
# HEADERS
# =========================

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
    "Mozilla/5.0 (X11; Linux x86_64)",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)"
]

# =========================
# READ LINKS
# =========================

with open(PDF_LINKS_FILE, "r", encoding="utf-8") as file:
    pdf_links = [
        line.strip()
        for line in file
        if line.strip()
    ]

# =========================
# MAIN LOOP
# =========================

for index, pdf_url in enumerate(pdf_links, start=1):

    delay = random.uniform(MIN_DELAY, MAX_DELAY)

    print(f"\n[{index}/{len(pdf_links)}]")
    print(f"[WAIT] {delay:.2f} sec")

    time.sleep(delay)

    try:

        headers = {
            "User-Agent": random.choice(USER_AGENTS),
            "Accept": "application/pdf,*/*",
            "Connection": "keep-alive"
        }

        print(f"[DOWNLOAD] {pdf_url}")

        response = session.get(
            pdf_url,
            headers=headers,
            timeout=REQUEST_TIMEOUT,
            stream=True
        )

        response.raise_for_status()

        content_type = response.headers.get(
            "Content-Type",
            ""
        ).lower()

        if "pdf" not in content_type:
            raise Exception(
                f"Not PDF content-type: {content_type}"
            )

        # =========================
        # FILENAME
        # =========================

        parsed_url = urlparse(pdf_url)

        filename = os.path.basename(
            parsed_url.path
        )

        if not filename.endswith(".pdf"):
            filename += ".pdf"

        # Удаляем опасные символы
        filename = filename.replace("?", "_")
        filename = filename.replace("&", "_")
        filename = filename.replace("=", "_")

        pdf_path = os.path.join(
            PDF_DIR,
            filename
        )

        # =========================
        # SAVE PDF
        # =========================

        with open(pdf_path, "wb") as pdf_file:
            for chunk in response.iter_content(1024 * 32):
                if chunk:
                    pdf_file.write(chunk)

        print(f"[SAVED PDF] {pdf_path}")

        # =========================
        # EXTRACT TABLES
        # =========================

        all_rows = []

        with pdfplumber.open(pdf_path) as pdf:

            for page_number, page in enumerate(pdf.pages):

                try:

                    tables = page.extract_tables()

                    for table in tables:

                        if not table or len(table) < 2:
                            continue

                        headers = table[0]
                        rows = table[1:]

                        for row in rows:

                            if not row:
                                continue

                            item = {}

                            for i in range(
                                min(
                                    len(headers),
                                    len(row)
                                )
                            ):

                                key = headers[i]

                                if key is None or key == "":
                                    key = f"column_{i}"

                                value = row[i]

                                if isinstance(value, str):
                                    value = value.strip()

                                item[
                                    str(key).strip()
                                ] = value

                            all_rows.append(item)

                except Exception as page_error:

                    print(
                        f"[PAGE ERROR] "
                        f"Page {page_number + 1}: "
                        f"{page_error}"
                    )

        # =========================
        # SAVE JSON
        # =========================

        json_path = os.path.join(
            JSON_DIR,
            filename.replace(".pdf", ".json")
        )

        with open(
            json_path,
            "w",
            encoding="utf-8"
        ) as json_file:

            json.dump(
                all_rows,
                json_file,
                ensure_ascii=False,
                indent=2
            )

        print(f"[SAVED JSON] {json_path}")

        # =========================
        # SAVE CSV
        # =========================

        if all_rows:

            df = pd.DataFrame(all_rows)

            csv_path = os.path.join(
                CSV_DIR,
                filename.replace(".pdf", ".csv")
            )

            df.to_csv(
                csv_path,
                index=False
            )

            print(f"[SAVED CSV] {csv_path}")

        else:

            print(
                "[WARNING] "
                "Таблицы не найдены"
            )

    except Exception as e:

        error_message = (
            f"{pdf_url} | {str(e)}"
        )

        print(f"[ERROR] {error_message}")

        with open(
            FAILED_LOG,
            "a",
            encoding="utf-8"
        ) as failed_file:

            failed_file.write(
                error_message + "\n"
            )

print("\nГотово")