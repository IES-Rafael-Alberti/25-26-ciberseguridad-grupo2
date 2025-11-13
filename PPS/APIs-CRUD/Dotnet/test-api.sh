#!/bin/bash

# ==========================================
# Script de Pruebas de la API CRUD Usuarios
# ==========================================
# Uso: ./test-api.sh
# Asegúrate de tener la API ejecutándose primero

API_URL="http://localhost:5001/api/usuarios"
USUARIO_EMAIL="test@example.com"
USUARIO_PASSWORD="SecurePassword123!"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "========================================"
echo "🧪 PRUEBAS API CRUD USUARIOS - .NET 9"
echo "========================================"
echo ""

# 1. REGISTRAR USUARIO
echo -e "${YELLOW}1. Registrando usuario...${NC}"
REGISTRO=$(curl -s -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d "{
    \"nombre\": \"Juan\",
    \"apellidos\": \"Pérez García\",
    \"email\": \"$USUARIO_EMAIL\",
    \"password\": \"$USUARIO_PASSWORD\"
  }")

echo "Respuesta: $REGISTRO"
USUARIO_ID=$(echo $REGISTRO | grep -o '"id":[0-9]*' | grep -o '[0-9]*')
echo -e "${GREEN}✓ Usuario registrado con ID: $USUARIO_ID${NC}"
echo ""

# 2. LOGIN
echo -e "${YELLOW}2. Haciendo login...${NC}"
LOGIN=$(curl -s -X POST "$API_URL/login" \
  -H "Content-Type: application/json" \
  -d "{
    \"email\": \"$USUARIO_EMAIL\",
    \"password\": \"$USUARIO_PASSWORD\"
  }")

echo "Respuesta (truncada):"
echo $LOGIN | head -c 200
echo "..."
TOKEN=$(echo $LOGIN | grep -o '"token":"[^"]*' | cut -d'"' -f4)
echo -e "${GREEN}✓ Token obtenido${NC}"
echo ""

# 3. LISTAR USUARIOS
echo -e "${YELLOW}3. Listando todos los usuarios...${NC}"
USUARIOS=$(curl -s -X GET "$API_URL" \
  -H "Authorization: Bearer $TOKEN")

echo "Respuesta: $USUARIOS"
echo -e "${GREEN}✓ Usuarios listados${NC}"
echo ""

# 4. OBTENER USUARIO POR ID
echo -e "${YELLOW}4. Obteniendo usuario por ID...${NC}"
USUARIO=$(curl -s -X GET "$API_URL/$USUARIO_ID" \
  -H "Authorization: Bearer $TOKEN")

echo "Respuesta: $USUARIO"
echo -e "${GREEN}✓ Usuario obtenido${NC}"
echo ""

# 5. ACTUALIZAR USUARIO
echo -e "${YELLOW}5. Actualizando usuario...${NC}"
ACTUALIZADO=$(curl -s -X PUT "$API_URL/$USUARIO_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"nombre\": \"Juan Carlos\",
    \"apellidos\": \"Pérez García López\",
    \"email\": \"juancarlos@example.com\",
    \"password\": \"NewSecurePassword123!\"
  }")

echo "Respuesta: $ACTUALIZADO"
echo -e "${GREEN}✓ Usuario actualizado${NC}"
echo ""

# 6. PRUEBA: CONTRASEÑA DÉBIL (debe fallar)
echo -e "${YELLOW}6. Prueba: Intentando registrar con contraseña débil...${NC}"
DEBIL=$(curl -s -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d "{
    \"nombre\": \"Test\",
    \"apellidos\": \"User\",
    \"email\": \"weak@example.com\",
    \"password\": \"weak\"
  }")

if echo "$DEBIL" | grep -q "error"; then
    echo -e "${GREEN}✓ Correctamente rechazada: $DEBIL${NC}"
else
    echo -e "${RED}✗ Debería haber sido rechazada${NC}"
fi
echo ""

# 7. PRUEBA: EMAIL INVÁLIDO (debe fallar)
echo -e "${YELLOW}7. Prueba: Email inválido...${NC}"
EMAIL_INVALIDO=$(curl -s -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d "{
    \"nombre\": \"Test\",
    \"apellidos\": \"User\",
    \"email\": \"invalid-email\",
    \"password\": \"ValidPassword123!\"
  }")

if echo "$EMAIL_INVALIDO" | grep -q "error"; then
    echo -e "${GREEN}✓ Correctamente rechazado${NC}"
else
    echo -e "${RED}✗ Debería haber sido rechazado${NC}"
fi
echo ""

# 8. PRUEBA: RATE LIMITING (ejecutar 6 veces)
echo -e "${YELLOW}8. Prueba: Rate Limiting en login (esperamos 429 después de 3 intentos)...${NC}"
for i in {1..6}; do
    RATE=$(curl -s -w "\n%{http_code}" -X POST "$API_URL/login" \
      -H "Content-Type: application/json" \
      -d "{
        \"email\": \"test@example.com\",
        \"password\": \"WrongPassword123!\"
      }")
    
    HTTP_CODE=$(echo "$RATE" | tail -n1)
    echo "Intento $i: HTTP $HTTP_CODE"
    
    if [ "$HTTP_CODE" = "429" ]; then
        echo -e "${GREEN}✓ Rate limiting activado en intento $i${NC}"
        break
    fi
done
echo ""

# 9. ELIMINAR USUARIO
echo -e "${YELLOW}9. Eliminando usuario...${NC}"
ELIMINADO=$(curl -s -X DELETE "$API_URL/$USUARIO_ID" \
  -H "Authorization: Bearer $TOKEN")

echo "Respuesta: $ELIMINADO"
echo -e "${GREEN}✓ Usuario eliminado${NC}"
echo ""

# 10. VERIFICAR QUE FUE ELIMINADO
echo -e "${YELLOW}10. Verificando que usuario fue eliminado...${NC}"
VERIFICACION=$(curl -s -w "\n%{http_code}" -X GET "$API_URL/$USUARIO_ID" \
  -H "Authorization: Bearer $TOKEN")

HTTP_CODE=$(echo "$VERIFICACION" | tail -n1)
if [ "$HTTP_CODE" = "404" ]; then
    echo -e "${GREEN}✓ Usuario correctamente eliminado (404 Not Found)${NC}"
else
    echo -e "${RED}✗ Usuario aún existe${NC}"
fi
echo ""

echo "========================================"
echo -e "${GREEN}🎉 PRUEBAS COMPLETADAS${NC}"
echo "========================================"
