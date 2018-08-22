# ChatBot s využitím Wit AI

ChatBot spolupracuje s API služby Wit AI a vrací odpovědi v následujících případech:

- odpověď na pozdrav
- dotaz na cenu známého nebo neznámého produktu
- nastavení připomínky

Chatbot komunikuje v angličtině. Má implementováno funkční rozhraní pro spolupráci s Facebook Messengerem (funkčnost podle dokumentace ověřena nástrojem [Postman](https://www.getpostman.com/)). 
Samotná spolupráce s Messengerem funkční bohužel není, protože se nepodařilo ve Facebookové [aplikaci](https://developers.facebook.com/apps/1788288764572523) navázané na [stránku](https://www.facebook.com/pg/Kopa-květáku-1376236125842886)
správně nastavit potřebná oprávnění (nicméně verifikace při registraci [webhooku](https://kvetak.zuzanakreizlova.cz/public/index.php) ve Facebookové aplikaci prošla bez problémů). 

Reakce robota je možné otestovat prostřednictvím [jednoduchého webového rozhraní](https://kvetak.zuzanakreizlova.cz/public/test.php)
simulujícího interakci s uživatelem prostřednictvím Messengeru. 
