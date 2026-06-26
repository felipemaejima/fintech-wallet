# 1. Buildar a imagem
sudo docker build -t fintech-app .

# 2. Parar o container antigo
sudo docker stop fintech 2>/dev/null; sudo docker rm fintech 2>/dev/null

# 3. Subir o novo (com .env via --env-file)
sudo docker run -d --name fintech \
  -e SERVER_NAME=":80" \
  --env-file .env \
  -p 80:80 \
  fintech-app

# 4. Acompanhar os logs do boot (migrations, caches, server)
sudo docker logs -f fintech