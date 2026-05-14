import re

def main():
    with open('rasp.html', 'r', encoding='utf-8') as file:
        html = file.read()
    
    links = re.findall(r'href=["\']([^"\']+)["\']', html)
    
    with open('links.txt', 'w', encoding='utf-8') as f:
        for link in links:
            f.write(link + '\n')

if __name__ == '__main__':
    main()