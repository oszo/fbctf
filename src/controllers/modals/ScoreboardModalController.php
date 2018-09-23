<?hh // strict

class ScoreboardModalController extends ModalController {
  public async function genGenerateIndicator(): Awaitable<:xhp> {
    $indicator = <div class="indicator game-progress-indicator"></div>;

    $game = await Configuration::gen('game');
    if ($game->getValue() === '1') {
      list($start_ts, $end_ts) = await \HH\Asio\va(
        Configuration::gen('start_ts'),
        Configuration::gen('end_ts'),
      );
      $start_ts = $start_ts->getValue();
      $end_ts = $end_ts->getValue();

      $seconds = intval($end_ts) - intval($start_ts);
      $s_each = intval($seconds / 10);
      $now = time();
      $current_s = intval($now) - intval($start_ts);
      $current = intval($current_s / $s_each);

      for ($i = 0; $i < 10; $i++) {
        $indicator_classes = 'indicator-cell ';
        if ($current >= $i) {
          $indicator_classes .= 'active ';
        }
        $indicator->appendChild(<span class={$indicator_classes}></span>);
      }
    } else {
      for ($i = 0; $i < 10; $i++) {
        $indicator->appendChild(<span class="indicator-cell"></span>);
      }
    }
    return $indicator;
  }

  <<__Override>>
  public async function genRender(string $_): Awaitable<:xhp> {
    $leaderboard_status = 0;
    $scoreboard_tbody = <tbody></tbody>;

    // If refresing is enabled, do the needful
    $gameboard = await Configuration::gen('gameboard');
    if ($gameboard->getValue() === '1') {
      $rank = 1;
      $leaderboard = await MultiTeam::genLeaderboard(false);
      $leaderboard_status = count($leaderboard);

      foreach ($leaderboard as $team) {
        $team_id = 'fb-scoreboard--team-'.strval($team->getId());
        $color = '#'.substr(md5($team->getName()), 0, 6).';';
        $style = 'color: '.$color.'; background:'.$color.';';
        list($quiz, $flag, $base) = await \HH\Asio\va(
          MultiTeam::genPointsByType($team->getId(), 'quiz'),
          MultiTeam::genPointsByType($team->getId(), 'flag'),
          MultiTeam::genPointsByType($team->getId(), 'base'),
        );
        $scoreboard_tbody->appendChild(
          <tr>
            <td style="width: 10%;" class="el--radio">
              <input
                type="checkbox"
                name="fb-scoreboard-filter"
                id={$team_id}
                value={$team->getName()}
                checked={true}
              />
              <label class="click-effect" for={$team_id}>
                <span style={$style}>FU</span>
              </label>
            </td>
            <td style="width: 10%;">{$rank}</td>
            <td style="width: 40%;">{$team->getName()}</td>
            <td style="width: 10%;">{strval($quiz)}</td>
            <td style="width: 10%;">{strval($flag)}</td>
            <td style="width: 10%;">{strval($base)}</td>
            <td style="width: 10%;">{strval($team->getPoints())}</td>
          </tr>
        );
        $rank++;
      }
    }
      
    $indicator = await $this->genGenerateIndicator();
    if($leaderboard_status === 0) {
      return
        <div class="fb-modal-content fb-row-container">
          <div class="modal-title row-fixed">
            <h4>{tr('scoreboard_')}</h4>
            <a href="#" class="js-close-modal">
              <svg class="icon icon--close">
                <use href="#icon--close" />
              </svg>
            </a>
          </div>
          <div class="scoreboard-graphic scoreboard-graphic-container">
            <div class="player-info" style="width:820px;height:220px;text-align:center;background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='28' height='49' viewBox='0 0 28 49'%3E%3Cg fill-rule='evenodd'%3E%3Cg id='hexagons' fill='%2321B4BA' fill-opacity='0.1' fill-rule='nonzero'%3E%3Cpath d='M13.99 9.25l13 7.5v15l-13 7.5L1 31.75v-15l12.99-7.5zM3 17.9v12.7l10.99 6.34 11-6.35V17.9l-11-6.34L3 17.9zM0 15l12.98-7.5V0h-2v6.35L0 12.69v2.3zm0 18.5L12.98 41v8h-2v-6.85L0 35.81v-2.3zM15 0v7.5L27.99 15H28v-2.31h-.01L17 6.35V0h-2zm0 49v-8l12.99-7.5H28v2.31h-.01L17 42.15V49h-2z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;);margin:auto;">
              <span class="player-rank" style="position:relative;float:left;top: 50%;font-size:xx-large;left:50%;transform:translate(-50%,-50%);">
                Scoreboard is Close
              </span>
            </div>
          </div>
          <div class="game-progress fb-progress-bar fb-cf row-fixed">
            {$indicator}
            <span class="label label--left">[{tr('Start')}]</span>
            <span class="label label--right">[{tr('End')}]</span>
          </div>
        </div>;
    } else {
      return
        <div class="fb-modal-content fb-row-container">
          <div class="modal-title row-fixed">
            <h4>{tr('scoreboard_')}</h4>
            <a href="#" class="js-close-modal">
              <svg class="icon icon--close">
                <use href="#icon--close" />
              </svg>
            </a>
          </div>
          <div class="scoreboard-graphic scoreboard-graphic-container">
            <svg
              class="fb-graphic"
              data-file="data/scores.php"
              width="820"
              height={220}>
            </svg>
          </div>
          <div class="game-progress fb-progress-bar fb-cf row-fixed">
            {$indicator}
            <span class="label label--left">[{tr('Start')}]</span>
            <span class="label label--right">[{tr('End')}]</span>
          </div>
          <div class="game-scoreboard fb-row-container">
            <table class="row-fixed">
              <thead>
                <tr>
                  <th style="width: 10%;">{tr('filter_')}</th>
                  <th style="width: 10%;">{tr('rank_')}</th>
                  <th style="width: 40%;">{tr('team_name_')}</th>
                  <th style="width: 10%;">{tr('quiz_pts_')}</th>
                  <th style="width: 10%;">{tr('flag_pts_')}</th>
                  <th style="width: 10%;">{tr('base_pts_')}</th>
                  <th style="width: 10%;">{tr('total_pts_')}</th>
                </tr>
              </thead>
            </table>
            <div class="row-fluid main-data">
              <table class="row-fixed">
                {$scoreboard_tbody}
              </table>
            </div>
          </div>
        </div>;
    }
  }
}
