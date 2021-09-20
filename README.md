Building occupancy tracker for contact tracing. Front-end allows users to check in and out of one of several buildings. Back-end allows admin to see who was in the same building at the same times during a given date range. 

Requires two MySQL tables: `users` and `sessions`

Users:
```
CREATE TABLE `users` (
  `username` varchar(50) CHARACTER SET latin1 NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8;

```

Sessions: 
```
CREATE TABLE `sessions` (
  `username` varchar(50) CHARACTER SET latin1 NOT NULL,
  `time_in` datetime NOT NULL,
  `time_out` datetime DEFAULT NULL,
  `building` varchar(255) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
```