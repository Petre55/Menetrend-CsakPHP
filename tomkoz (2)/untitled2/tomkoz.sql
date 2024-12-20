-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2024. Nov 23. 20:06
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `tomkoz`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `bejelentes`
--

CREATE TABLE `bejelentes` (
  `id` int(100) NOT NULL,
  `megallonev` varchar(50) NOT NULL,
  `jaratszam` int(100) NOT NULL,
  `datum` datetime NOT NULL,
  `fel_email` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `bejelentes`
--

INSERT INTO `bejelentes` (`id`, `megallonev`, `jaratszam`, `datum`, `fel_email`) VALUES
(4, 'Felső Tisza-part', 2, '2024-11-23 19:58:01', 'Admin1234@gmail.com'),
(5, 'Felső Tisza-part', 2, '2024-11-23 19:58:23', 'Admin1234@gmail.com');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `felhasznalo`
--

CREATE TABLE `felhasznalo` (
  `nev` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `jelszo` varchar(60) NOT NULL,
  `szerep` int(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `felhasznalo`
--

INSERT INTO `felhasznalo` (`nev`, `email`, `jelszo`, `szerep`) VALUES
('Admin1234', 'Admin1234@gmail.com', '$2y$10$D5tTUyA4iQBR18zSnictMubyU0hQ1G9SMCSllgQNNUZDAvZ.0qZ5K', 2),
('Aszfalt0', 'Aszfalt0@gmail.com', '$2y$10$NJOvyuWtV.IW0KYAgzklD.vDoEZlT//WY8TyVD5gyHvIh9HUffpUW', 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `jarat`
--

CREATE TABLE `jarat` (
  `jaratszam` int(100) NOT NULL,
  `kezdom` varchar(50) NOT NULL,
  `vegm` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `jarat`
--

INSERT INTO `jarat` (`jaratszam`, `kezdom`, `vegm`) VALUES
(1, 'Baktói bekötőút', 'Szeged Baktó, végállomás'),
(2, 'Felső Tisza-part', 'Mars tér'),
(3, 'Baktói bekötőút', 'Rókó utca'),
(4, 'Szeged Baktó, végállomás', 'Tavasz utca'),
(99, 'Tisza Lajos körút', 'Tavasz utca');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `megallo`
--

CREATE TABLE `megallo` (
  `nev` varchar(50) NOT NULL,
  `x` decimal(9,6) DEFAULT NULL,
  `y` decimal(9,6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `megallo`
--

INSERT INTO `megallo` (`nev`, `x`, `y`) VALUES
('Aradi vértanúk tere', 46.253120, 20.140560),
('Baktói bekötőút', 46.266110, 20.148560),
('Dugonics tér', 46.245500, 20.145500),
('Felső Tisza-part', 46.246220, 20.145890),
('Híd utca', 46.246500, 20.143500),
('Kossuth Lajos sugárút', 46.248850, 20.146280),
('Liget', 46.263440, 20.145780),
('Mars tér', 46.257633, 20.141171),
('Műkert út', 46.241500, 20.149500),
('Rókó utca', 46.242500, 20.147500),
('Széchenyi tér', 46.248500, 20.148500),
('Szeged Baktó, végállomás', 46.270480, 20.152120),
('Tavasz utca', 46.244500, 20.146500),
('Tisza Lajos körút', 46.249830, 20.144220),
('Újszegedi vasútállomás', 46.259050, 20.142910),
('Vásárhelyi Pál utca', 46.247500, 20.147500),
('Zrínyi utca 4-8', 46.243500, 20.152500);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `menetrend`
--

CREATE TABLE `menetrend` (
  `id` int(100) NOT NULL,
  `jaratszam` int(100) NOT NULL,
  `megallonev` varchar(50) NOT NULL,
  `sorszam` int(100) NOT NULL,
  `idopont` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `menetrend`
--

INSERT INTO `menetrend` (`id`, `jaratszam`, `megallonev`, `sorszam`, `idopont`) VALUES
(27, 1, 'Baktói bekötőút', 1, '11:00:00'),
(28, 1, 'Felső Tisza-part', 5, '12:00:00'),
(31, 1, 'Aradi vértanúk tere', 2, '11:05:00'),
(32, 1, 'Kossuth Lajos sugárút', 3, '11:10:00'),
(33, 1, 'Liget', 4, '11:30:00'),
(35, 1, 'Szeged Baktó, végállomás', 7, '20:00:00'),
(49, 1, 'Tisza Lajos körút', 6, '13:00:00'),
(51, 2, 'Baktói bekötőút', 5, '12:00:00'),
(53, 2, 'Tisza Lajos körút', 2, '11:12:00'),
(54, 2, 'Liget', 3, '11:13:00'),
(55, 2, 'Szeged Baktó, végállomás', 4, '11:20:00'),
(59, 2, 'Felső Tisza-part', 1, '10:00:00'),
(63, 3, 'Baktói bekötőút', 1, '11:00:00'),
(64, 3, 'Rókó utca', 4, '22:00:00'),
(65, 3, 'Zrínyi utca 4-8', 2, '11:22:00'),
(66, 3, 'Újszegedi vasútállomás', 3, '12:22:00'),
(67, 4, 'Szeged Baktó, végállomás', 1, '12:00:00'),
(68, 4, 'Vásárhelyi Pál utca', 3, '15:00:00'),
(69, 4, 'Tavasz utca', 4, '15:00:00'),
(71, 4, 'Aradi vértanúk tere', 2, '14:50:00'),
(72, 2, 'Zrínyi utca 4-8', 6, '15:20:00'),
(73, 2, 'Mars tér', 7, '22:22:00'),
(74, 99, 'Tisza Lajos körút', 1, '22:22:00'),
(75, 99, 'Tavasz utca', 4, '23:59:00'),
(76, 99, 'Aradi vértanúk tere', 3, '23:55:00'),
(77, 99, 'Baktói bekötőút', 2, '22:55:00');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `bejelentes`
--
ALTER TABLE `bejelentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jara` (`jaratszam`),
  ADD KEY `megall` (`megallonev`),
  ADD KEY `fel_email` (`fel_email`);

--
-- A tábla indexei `felhasznalo`
--
ALTER TABLE `felhasznalo`
  ADD PRIMARY KEY (`email`);

--
-- A tábla indexei `jarat`
--
ALTER TABLE `jarat`
  ADD PRIMARY KEY (`jaratszam`),
  ADD KEY `kezdom` (`kezdom`),
  ADD KEY `vegm` (`vegm`);

--
-- A tábla indexei `megallo`
--
ALTER TABLE `megallo`
  ADD PRIMARY KEY (`nev`);

--
-- A tábla indexei `menetrend`
--
ALTER TABLE `menetrend`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `jarat` (`jaratszam`),
  ADD KEY `megallo` (`megallonev`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `bejelentes`
--
ALTER TABLE `bejelentes`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT a táblához `menetrend`
--
ALTER TABLE `menetrend`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `bejelentes`
--
ALTER TABLE `bejelentes`
  ADD CONSTRAINT `fel_email` FOREIGN KEY (`fel_email`) REFERENCES `felhasznalo` (`email`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jara` FOREIGN KEY (`jaratszam`) REFERENCES `jarat` (`jaratszam`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `megall` FOREIGN KEY (`megallonev`) REFERENCES `megallo` (`nev`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Megkötések a táblához `jarat`
--
ALTER TABLE `jarat`
  ADD CONSTRAINT `kezdm` FOREIGN KEY (`kezdom`) REFERENCES `megallo` (`nev`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `vegm` FOREIGN KEY (`vegm`) REFERENCES `megallo` (`nev`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Megkötések a táblához `menetrend`
--
ALTER TABLE `menetrend`
  ADD CONSTRAINT `jarat` FOREIGN KEY (`jaratszam`) REFERENCES `jarat` (`jaratszam`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `megallo` FOREIGN KEY (`megallonev`) REFERENCES `megallo` (`nev`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
