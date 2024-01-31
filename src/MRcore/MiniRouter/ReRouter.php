<?php

namespace MiniRouter;

interface ReRouter{
	/**
	 * @param string $path
	 * @return bool Devuelve TRUE para indicar que la ruta debe cambiar
	 */
	function change(string $path): bool;

	/**
	 * Devuelve la nueva ruta
	 * @return string|null Si retorna NULL, se debe usar el path original
	 */
	public function newPath(): ?string;

}