<?php


class Netstats {

	public function ping($targets) {
		$pingCmd = 'ping -c 5 -w 1 -n -i 0.2 ';
		foreach ($targets As $targer) {
			$cmd = $cmd.$pingCmd.escapeshellcmd($target).';';
		}
		echo $cmd;

		/*
 --- 82.83.205.169 ping statistics ---
5 packets transmitted, 5 received, 0% packet loss, time 804ms
rtt min/avg/max/mdev = 21.603/21.727/21.835/0.074 ms
hendrik@stendhalgame:/srv/faiumoni-documents$ ping -U -c 5 -w 1 -n -i 0.2 82.83.205.120
PING 82.83.205.120 (82.83.205.120) 56(84) bytes of data.
64 bytes from 82.83.205.120: icmp_seq=1 ttl=56 time=28.8 ms
64 bytes from 82.83.205.120: icmp_seq=2 ttl=56 time=28.9 ms
64 bytes from 82.83.205.120: icmp_seq=3 ttl=56 time=28.7 ms
64 bytes from 82.83.205.120: icmp_seq=4 ttl=56 time=28.9 ms
64 bytes from 82.83.205.120: icmp_seq=5 ttl=56 time=29.0 ms

--- 82.83.205.120 ping statistics ---
5 packets transmitted, 5 received, 0% packet loss, time 804ms
rtt min/avg/max/mdev = 28.768/28.921/29.008/0.174 ms
hendrik@stendhalgame:/srv/faiumoni-documents$ ping -U -c 5 -w 1 -n -i 0.2 83.83.205.120
PING 83.83.205.120 (83.83.205.120) 56(84) bytes of data.

--- 83.83.205.120 ping statistics ---
5 packets transmitted, 0 received, 100% packet loss, time 828ms

		 */
	}

	public function traceroute($ip, $fast, $count) {
		if ($fast) {
			$cmd = 'traceroute -I -q '.intval($count).' -w 1 -n '.escapeshellcmd($ip);
			$res = shell_exec($cmd);
		} else {
			$cmd = 'traceroute -I -q '.intval($count).' -w 1 -A '.escapeshellcmd($ip);
			$res = shell_exec($cmd);
		}
		echo $this->parseTraceroute($res, $count);
	}

	private function parseTraceroute($data, $count) {
		$lines = explode("\n", $data);
		$res = array();
		foreach ($lines as $line) {
			if (strpos($line, 'traceroute to') !== false) {
				continue;
			}
			$res = array_merge($res, $this->parseLine($line, $count));
		}
		$res = $this->removeTrailingTimeouts($res);
		return $this->tracerouteToHtml($res, $count);
	}

	public function removeTrailingTimeouts($lines) {
		$temp = 0;
		for ($i = count($lines) - 1; $i > 0; $i--) {
			if ($lines[$i]->ip != '') {
				$temp = $i + 1;
				break;
			}
		}
		return array_slice($lines, 0, $temp + 1);
	}

	public function tracerouteToHtml($traceroute, $count) {
		$html = '<table class="prettytable">';
		$html .= '<tr><th>No</th><th>Name/IP</th><th>AS</th>';
		for ($i = 0; $i < $count; $i++) {
			$html .= '<th>ms</th>';
		}
		for ($iRow = 0; $iRow < count($traceroute); $iRow++) {
			$line = $traceroute[$iRow];

			// magic depending on the hop number
			if ($line->no % 2 == 0) {
				$style = ' class="even"';
			} else {
				$style = ' class="odd"';
			}
			$html .= '<tr'.$style.'>';

			if (($iRow == 0) || $traceroute[$iRow-1]->no != $line->no) {
				$cont = 0;
				while ($iRow + $cont < count($traceroute)) {
					if ($traceroute[$iRow + $cont]->no != $line->no) {
						break;
					}
					$cont++;
				}

				if ($cont > 0) {
					$cnt = ' rowspan="'.$cont.'"';
				} else {
					$cnt = '';
				}
				$html .= '<td '.$cnt.'>'.htmlspecialchars($line->no).'</td>';
			}

			// name and IP
			$html .= '<td>';
			if (isset($line->name)) {
				$html .= htmlspecialchars($line->name).'<br>';
			}
			$html .= htmlspecialchars($line->ip).'</td><td>';

			// AS
			if (isset($line->as) && strlen($line->as) > 2) {
				$temp = $line->as;
				$temp = substr($temp, 1, strlen($temp) - 2);
				$ases = explode('/', $temp);
				foreach ($ases As $as) {
					$html .= '[<a target="_blank" href="http://bgp.he.net/'.urlencode($as).'">'.htmlspecialchars($as).'</a>] ';
				}
			} else {
				$html .= '&nbsp;';
			}
			$html .=  '</td>';

			// times, on hops with multiple ips, indent the results
			for ($i = 0; $i < $line->pre; $i++) {
				$html .= '<td>&nbsp;</td>';
			}
			$c = $line->pre;
			foreach ($line->times As $time) {
				$html .= '<td>'.htmlspecialchars($this->formatTime($time)).'</td>';
				$c++;
			}
			for ($i = $c; $i < $count; $i++) {
				$html .= '<td>&nbsp;</td>';
			}

			$html .= '</tr>'."\r\n";
		}
		$html .= '</table>';
		return $html;
	}

	private function formatTime($time) {
		if (!is_numeric($time)) {
			return $time;
		}
		return round($time, 1);
	}

	public function parseLine($line, $pingCount = 3) {

		$line = preg_replace('/  /', ' ', $line);

		$pre = 0;   // the number of times that have been filed in the previous line
		$iPre = 0;  // number of times that have been printed in this line before the IP address
		$idx = 0;   // line index
		$res = array();
		$tokens = explode(" ", trim($line));
		if (trim($line) == '') {
			return $res;
		}
		$i = 1;

		while (true) {
			$res[$idx] = new TracerouteLine();

			// parse line number, IP, name and AS
			$res[$idx]->no = $tokens[0];
			while ($this->isErrorToken($tokens[$i])) {
				$res[$idx]->times[] = $tokens[$i];
				$iPre++;
				$i++;

				// 7 * * *
				if ($i == count($tokens)) {
					$res[$idx]->ip = '';
					$res[$idx]->pre = 0;
					break 2;
				}
			}
			$res[$idx]->ip = $tokens[$i];
			$i++;
			if (strpos($tokens[$i], '(') !== false) {
				$res[$idx]->name = $res[$idx]->ip;
				$res[$idx]->ip = $tokens[$i];
				$i++;
			}
			if (strpos($tokens[$i], '[') !== false) {
				$res[$idx]->as = $tokens[$i];
				$i++;
			}

			// parse time, and detect new internal line because of an answer from a different host
			$time = $tokens[$i];
			$res[$idx]->pre = $pre;

			while (true) {
				$res[$idx]->times[] = $time;
				$pre++;
				$i++;
				if (!$this->isErrorToken($time)) {
					$i++;
				}
				if ($pre+$iPre == $pingCount) {
					break 2;
				}

				// 2nd time-token
				$time = $tokens[$i];
				if (!$this->isErrorToken($time) && !is_numeric($time)) {
					$idx++;
					continue 2;
				}
			}
		}
		return $res;
	}

	public function isErrorToken($token) {
		return (($token == "*") || (strpos($token, '!') === 0));
	}
}


class TracerouteLine {
	public $no;
	public $name;
	public $ip;
	public $as;
	public $pre;
	public $times = array();

	public static function create($no, $name, $ip, $as,  $pre, $times) {
		$res = new TracerouteLine();
		$res->no = $no;
		$res->name = $name;
		$res->ip = $ip;
		$res->as = $as;
		$res->pre = $pre;
		$res->times = $times;
		return $res;
	}

	public function __toString() {
		return var_export($this, true);
	}
}
