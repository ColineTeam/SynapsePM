# SynapsePM (Not Worked) 
 
В этом репозитории  вы сможете найти попытки реализовать рабочий протокол Synapse от [iTXTech](https://github.com/iTXTech), в ветке 
 [ported-java ](https://github.com/ColineTeam/SynapsePM/tree/ported-java) находятся портированные файлы java на php с репозитория [SynapseAPI](https://github.com/iTXTech/SynapseAPI).   
 Рабочего еффекта не удалось получить поскольку: 
 * Не получилось передать класс в AsyncTask, так как это сделано в [SynapseEntry.java](https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/SynapseEntry.java#L192), если не использовать AsyncTask тотогда `sleep` будет торомозить сервер 
 * Не удалось найти максимально похожую библиотеку к [Netty](http://netty.io) (используеться для реализации TCP соиденения) для PHP, в сдлествии чего решили использовать то что реализовано в [SynapsePM](https://github.com/iTXTech/SynapsePM)    
  
 *Пожалуйста, если вам удастся реализовать Synapse для PocketMine пожалуйста поделитесь этим*
