import socket
import time
import requests

ip = 'aezamine.com' # айпи / ip server
port = 19132 # порт / port server
status = None
txt = f"🟠 Подключение к серверу {ip}:{port}"

# Отправка сообщений в Телеграм, ВК / Send message to Telegram, VK
def send(message=''):
    token = '6948049317:token'  # токен бота / token bot
    chat_id = '-1002'  # айди чата / chat id

    telegram_url = f'https://api.telegram.org/{token}/sendMessage?chat_id={chat_id}&text={message}'
    requests.get(telegram_url)

    #vk_url = 'https://api.vk.com/method/messages.send'
    #vk_params = {
        #'access_token': 'vk1.a.',  # токен / token
        #'v': '5.85',
        #'message': message,
        #'chat_id': 1  # айди чата / id chat
    #}
    #requests.get(vk_url, params=vk_params)

send(txt)
print(txt)

# ЗАПУСК / START
while True:
    try:
        socket_udp = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        socket_udp.settimeout(0.1)
        request_packet = b"\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78\x00\x00\x00\x00\x00\x00\x00\x00"
        socket_udp.sendto(request_packet, (ip, port))
        response = socket_udp.recvfrom(4096)
        server_players = response[0][35:].decode('utf-8')
        server_players = server_players.split(';')
        socket_udp.close()
        numplayers = int(server_players[4]) if len(server_players) > 4 else 0
            
        if status is not False:
            status = True
            txt = f"[{time.strftime('%d-%m-%Y %H:%M:%S')}/{port}] > Статус сервера изменился на 🟢\nОнлайн: {numplayers}"
            send(txt)
            print(txt)
        
    except socket.timeout:
        if status is not True:
            status = False
            txt = f"[{time.strftime('%d-%m-%Y %H:%M:%S')}/{port}] > Статус сервера изменился на 🔴 (response)"
            send(txt)
            print(txt)
        
    except socket.gaierror:
        if status is not True:
            status = False
            txt = f"[{time.strftime('%d-%m-%Y %H:%M:%S')}/{port}] > Статус сервера изменился на 🔴"
            send(txt)
            print(txt)
    
    time.sleep(5)  # время проверки / time check
