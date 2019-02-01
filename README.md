# Modul CardCadou
## Magento 1


**Instalare modul**


Primul lucru pe care trebuie sa-l faceti atunci cand instalati modulul este sa dezarhivati arhiva in care este stocat modulul si sa copiati folderul app si lib in directorul radacina al magazinului utilizand FTP.

Apoi, mergeti la panoul de administrare Magento si parcurgeti urmatorii pasi pentru a va asigura ca procesul se desfasoara fara probleme:

1. Verificati daca aveti un backup al site-ului dvs. sau creati unul prin accesarea
   System - Tools - Backups.
2. Dezactivati compilarea si stergeti cacheul accesand System - Cache Management.
3. Relogati-va in panoul de administrare .
4. Accessati System - Configuration - Sales - Payment methods pentru a configura extensia


**Configurare modul**


Configurarea modulului se afla in sectiunea Payment Methods cu urmatoarele optiuni:


Pentru functionarea corecta a modului, toate campurile se vor seta dupa cum urmeaza:
Enable - optiune pentru a activa / dezactiva modulul.
Partner Code - codul partener pus la dispozitie de CardCadou.
Secrety Key - codul secret pus la dispozitie de CardCadou.
Method Name - numele sub care va aparea plata prin CardCadou.
Method Call To Action - textul care va aparea pe butonul de aplicare card.
Accepted When Order Status - statusul comenzilor la care utilizarea cardului cadou se va confirma.
Canceled When Order Status - statusul comenzilor la care cardul cadou se va anula/debloca.
Timeout - numarul de minute dupa care cardul cadou se va debloca in cazul in care nu s-a confirmat/anulat
