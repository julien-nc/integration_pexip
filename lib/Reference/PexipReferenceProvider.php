<?php
/**
 * @copyright Copyright (c) 2023 Julien Veyssier <julien-nc@posteo.net>
 *
 * @author Julien Veyssier <julien-nc@posteo.net>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Pexip\Reference;

use OCP\Collaboration\Reference\ADiscoverableReferenceProvider;
use OCP\Collaboration\Reference\Reference;
use OCA\Pexip\AppInfo\Application;
use OCA\Pexip\Service\PexipService;
use OCP\Collaboration\Reference\IReference;
use OCP\IConfig;
use OCP\IL10N;

use OCP\IURLGenerator;

class PexipReferenceProvider extends ADiscoverableReferenceProvider  {

	private const RICH_OBJECT_TYPE = Application::APP_ID . '_call';

	private PexipService $pexipService;
	private ?string $userId;
	private IL10N $l10n;
	private IURLGenerator $urlGenerator;
	private IConfig $config;

	public function __construct(PexipService $pexipService,
								IL10N $l10n,
								IConfig $config,
								IURLGenerator $urlGenerator,
								?string $userId) {
		$this->pexipService = $pexipService;
		$this->userId = $userId;
		$this->l10n = $l10n;
		$this->urlGenerator = $urlGenerator;
		$this->config = $config;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string	{
		return 'pexip-call';
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return $this->l10n->t('Pexip calls');
	}

	/**
	 * @inheritDoc
	 */
	public function getOrder(): int	{
		return 10;
	}

	/**
	 * @inheritDoc
	 */
	public function getIconUrl(): string {
		return $this->urlGenerator->getAbsoluteURL(
			$this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg')
		);
	}

	/**
	 * @inheritDoc
	 */
	public function matchReference(string $referenceText): bool {
		return $this->getPexipId($referenceText) !== null;
	}

	/**
	 * @inheritDoc
	 */
	public function resolveReference(string $referenceText): ?IReference {
		if ($this->matchReference($referenceText)) {
			$pexipId = $this->getPexipId($referenceText);
			if ($pexipId === null) {
				return null;
			}

			$reference = new Reference($referenceText);
			$callInfo = $this->pexipService->getPexipCallInfo($pexipId);
			$reference->setRichObject(
				self::RICH_OBJECT_TYPE,
				[
					'call' => $callInfo,
				]
			);
			return $reference;
		}

		return null;
	}

	/**
	 * @param string $url
	 * @return array|null
	 */
	private function getPexipId(string $url): ?string {
		$pexipUrl = $this->config->getAppValue(Application::APP_ID, 'pexip_url');
			$this->urlGenerator->getAbsoluteURL('/apps/' . Application::APP_ID);

		// link example: https://pexip.example/webapp3/m/3jf5wq3hibbqvickir7ysqehfi
		preg_match('/^' . preg_quote($pexipUrl, '/') . '\/webapp3\/m\/([0-9a-z]+)$/i', $url, $matches);
		if (count($matches) > 1) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getCachePrefix(string $referenceId): string {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getCacheKey(string $referenceId): ?string {
		return $referenceId;
	}
}
