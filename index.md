## MonrifNet QN Local

Plugin WordPress di [Monrif Net](http://www.monrif.net) per siti del network [Quotidiano.net](http://www.quotidiano.net).

### Contenuti Plugin WP

- [Header network](#netheader)
- [Aggiornamento e configurazione](#config)

<a name="netheader"></a>

### Header network

Mostra un mini-header in cima alle pagine del sito con link verso il resto del network.

**IMPORTANTE:** nel caso si usino plugin di ottimizzazione delle risorse come _Autoptimize_,
sarà necessario escludere _header.css_ per evitare che le modifiche non vengano ignorate
in seguito ad un aggiornamento di questo plugin. In alternativa, ricordarsi di svuotare
la cache delle risorse ottimizzate dopo l'aggiornamento di questo plugin.

<a name="config"></a>

### Aggiornamento e configurazione

#### Aggiornamento del plugin via pannello wp-admin

Il plugin controlla ad intervalli regolari la disponibilità di nuove versioni dalla
repository GitHub. Per farlo, ha bisogno delle seguenti librerie attive:

- php_curl
- php_openssl
- php_zip

Inoltre, la cartella _wp-content/plugins_ dovrà avere permessi a 777.
Se ciò non fosse possibile, gli aggiornamenti andranno fatti manualmente (v. sotto)

Quando una nuova versione è disponibile, compare una notifica in cima al pannello wp-admin.

Per avviare l'aggiornamento, aprire la pagina **Impostazioni -> QN Local** e cliccare il pulsante _[Aggiorna manualmente]_

Se la procedura dovesse incontrare dei problemi, è consigliabile seguire la procedura manuale qua sotto.

#### Configurazione aggiornamenti automatici

Dalla pagina **Impostazioni -> QN Local** del pannello wp-admin è possibile inoltre configurare la
frequenza (espressa in minuti) con cui il plugin verifica la disponibilità di aggiornamenti,
da un intervallo minimo di un'ora (default) ad un massimo di un giorno.

Selezionando il checkbox _Aggiorna automaticamente_, il plugin tenterà di aggiornare i propri file nonappena
individuerà la presenza di una nuova versione, senza così richiedere alcun ulteriore intervento umano.

#### Aggiornamento manuale del plugin via filesystem

Nel caso non siano disponibili le librerie PHP elencate al punto precedente,
il plugin va aggiornato manualmente copiando i contenuti della repository GitHub (scaricabile come archivio Zip.)

Si consiglia, laddove gli aggiornamenti automatici o da pannello wp-admin non siano attivi,
di verificare la disponibilità di aggiornamenti almeno una volta alla settimana.
