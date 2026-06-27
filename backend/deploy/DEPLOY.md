# 1. Buildar a imagem
docker build -f deploy/Dockerfile -t fintech-app .

# 2. Parar o container antigo
docker stop fintech 2>/dev/null; docker rm fintech 2>/dev/null

# 3. Subir o novo (com .env via --env-file)
docker run -d --name fintech \
  -e SERVER_NAME="18.190.159.168.nip.io" \
  --env-file .env \
  -p 80:80 \
  -p 443:443 \
  -p 443:443/udp \
  fintech-app

# 4. Acompanhar os logs do boot (migrations, caches, server)
docker logs -f fintech
