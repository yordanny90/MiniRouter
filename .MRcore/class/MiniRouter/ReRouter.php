<?php

namespace MiniRouter;

abstract class ReRouter{
	/**
	 * Valida si la ruta se debe reencaminar.
	 *
	 * Si la ruta debe cambiar, el nuevo method y path se obtienen por {@see ReRouter::getMethod()} y {@see ReRouter::getPath()}, respectivamente
	 * @param string $method
	 * @param string $path
	 * @return bool Devuelve TRUE para indicar que la ruta debe cambiar
	 */
	abstract function change(string $method, string $path): bool;

	/**
	 * @return string|null Si retorna NULL, se debe usar el method original
	 */
	abstract public function getMethod(): ?string;

	/**
	 * @return string|null Si retorna NULL, se debe usar el path original
	 */
	abstract public function getPath(): ?string;

}